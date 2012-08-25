<?php

namespace zc\lib;

use \esprit\core\db\DatabaseManager;
use \esprit\core\Cache;
use \esprit\core\util\Logger;
use \esprit\core\Request;

/**
 * Use this class for retrieving ChatSession objects.
 *
 * This class caches objects in the universal Cache object. If a chat session
 * is not cached, it will query the database.
 *
 * @author jbowens
 */
class ChatSessionSource {

    const LOG_SOURCE = "ChatSessionSource";
    const SESSIONID_CACHE_NAMESPACE = "chat_sessions";
    const PER_ROOM_CACHE_NAMESPACE = "per_room_sessions";
    const BAD_ID_EXPIRE_TIME_DELTA = 30;
    const CHAT_SESSION_EXPIRE_TIME_DELTA = 20;
    const CHAT_SESSION_VARIABLE_PREFIX = "chatsession_";
    
    const SQL_CHAT_SESSION_BY_ID = "SELECT * FROM `chat_sessions` WHERE `chatSessionid` = ?";
    const SQL_CREATE_CHAT_SESSION = "INSERT INTO `chat_sessions` (`roomid`, `username`, `lastPing`, `loginTime`, `assignedGuestId`) VALUES(?, ?, ?, ?, ?)";
    const SQL_SELECT_LAST_GUEST_NUMBER = "SELECT `lastGuestNumber` FROM `rooms` WHERE `roomid` = ?";
    const SQL_UPDATE_LAST_GUEST_NUMBER = "UPDATE `rooms` SET `lastGuestNumber` = ? WHERE `roomid` = ?";
    const SQL_GET_ALL_SESSIONS = "SELECT * FROM `chat_sessions` WHERE `roomid` = ?";
    const SQL_REACTIVE_CHAT_SESSION = "UPDATE `chat_sessions` SET `active` = 1 WHERE `chatSessionid` = ?";

    protected $dbm;
    protected $logger;
    protected $sessionidCache;
    protected $perRoomCache;

    public function __construct( DatabaseManager $dbm, Logger $logger, Cache $cache )
    {
        $this->dbm = $dbm;
        $this->logger = $logger;
        $this->sessionidCache = $cache->accessNamespace( self::SESSIONID_CACHE_NAMESPACE );
        $this->perRoomCache = $cache->accessNamespace( self::PER_ROOM_CACHE_NAMESPACE );
    }

    /**
     * Retrieves a chat session by its chat session id. This id is usually stored
     * in session data.
     *
     * @param $chatSessionid  the id of the chat session
     * @return a ChatSession object corresponding to the given id
     */
    public function getChatSessionById( $chatSessionId )
    {
        if( $this->sessionidCache->isCached( $chatSessionId ) )
        {
            // Cache hit
            return $this->sessionidCache->get( $chatSessionId );
        }
        else
        {
            // Cache miss
            $db = $this->dbm->getDb();
            $pstmt = $db->prepare( self::SQL_CHAT_SESSION_BY_ID );
            $pstmt->execute( array( $chatSessionId ) );
            $sessionAssoc = $pstmt->fetch( \PDO::FETCH_ASSOC );

            if( $sessionAssoc == null )
            {
                // This session doesn't exist!
                $this->logger->warning( "Chat session id ".$chatSessionId." was queried, but it doesn't exist", self::LOG_SOURCE );
                // Still cache it, to mitigate the effect of DDOS attacks using bad session ids
                $this->sessionidCache->set( $chatSessionId, null, time() + self::BAD_ID_EXPIRE_TIME_DELTA );
                return null;
            }
            else
            {
                // Valid session id
                $chatSession = ChatSession::createFromArray( $sessionAssoc ); 
                $this->sessionidCache->set( $chatSessionId, $chatSession, time() + self::CHAT_SESSION_EXPIRE_TIME_DELTA );
                return $chatSession;
            }
        }
    }

    /**
     * Creates a chat session in the given room.
     *
     * @param $room the Room to create the session in
     * @return ChatSession the newly created chat session
     */
    public function createSession( Room $room )
    {
        $db = $this->dbm->getDb();

        $guestUserId = $this->assignGuestId( $room );
        $username = 'Guest' . $guestUserId;

        $pstmt = $db->prepare( self::SQL_CREATE_CHAT_SESSION );
        $now = time();
        $pstmt->execute(array( $room->getRoomId(),
                               $username,
                               $now,
                               $now,
                               $guestUserId ));
        $chatSession = ChatSession::getBuilder()->chatSessionId( $db->lastInsertId() )
                                                ->roomId( $room->getRoomId() )
                                                ->username( $username )
                                                ->lastPing( $now )
                                                ->loginTime( $now )
                                                ->assignedGuestid( $guestUserId )
                                                ->build();
        return $chatSession;
    }

    /**
     * Returns a guest user id unique to this room. This function
     * will return a unique guest id every time it's called with the
     * same room.
     *
     * @param $room the Room to get a guest id for
     * @return a room unique guest id
     */
    public function assignGuestId( Room $room )
    {
        $db = $this->dbm->getDb();
        $transactionBegan = $db->beginTransaction();
        if( ! $transactionBegan )
        {
            $this->logger->error( "Unable to begin transaction when assigning guest id", self::LOG_SOURCE );
            return 1;
        }

        $selectStmt = $db->prepare( self::SQL_SELECT_LAST_GUEST_NUMBER );
        $selectStmt->execute(array( $room->getRoomId() ));
        $lastGuestId = $selectStmt->fetchColumn();
        $newGuestId = $lastGuestId + 1;
        
        // Update room
        $updateStmt = $db->prepare( self::SQL_UPDATE_LAST_GUEST_NUMBER );
        $updateStmt->execute(array( $newGuestId, $room->getRoomId() ));

        $commitSuccess = $db->commit();
        if( ! $commitSuccess )
        {
            $this->logger->error( "Unable to commit transaction when assigning guest id", self::LOG_SOURCE );
            $db->rollBack();
        }

        return $newGuestId;
    }

    /**
     * Retrieves all of the existing chat sessions in the given room.
     *
     * @param $room  the Room to retrieve chat sessions for
     * @return an array of Room objects
     */
    public function getChatSessions(Room $room)
    {
        if( $this->perRoomCache->isCached( $room->getRoomId() ) )
        {
            // Cache hit
            return $this->perRoomCache->get( $room->getRoomId() );
        }
        else
        {
            // Cache miss
            $this->logger->info("Cache miss getting the chat sessions for room " . $room->getRoomId(), self::LOG_SOURCE);
            $db = $this->dbm->getDb();
            $pstmt = $db->prepare( self::SQL_GET_ALL_SESSIONS );
            $pstmt->execute(array( $room->getRoomId() ));
            $chatSessions = array();
            while( $chatSessionArray = $pstmt->fetch(\PDO::FETCH_ASSOC) )
            {
                array_push($chatSessions, ChatSession::createFromArray( $chatSessionArray ));
            }
            // recache
            $this->setRoomSessions( $room, $chatSessions );
            // return the sessions
            return $chatSessions;
        }
    }

    /**
     * Reactivates a chat session.
     *
     * @param $chatSession  the chat session to reactivate
     */
    public function reactivateChatSession( ChatSession $chatSession )
    {
        if( $chatSession->getActive() )
        {
            $this->logger->error( "Asked to reactive an active chat session.", self::LOG_SOURCE );
        }

        $chatSession->setActive( true );
        $chatSession->updateLastPing( $chatSession );
        // Inform the database
        $db = $this->dbm->getDb();
        $stmt = $db->prepare( self::SQL_REACTIVE_CHAT_SESSION );
        $stmt->execute(Array( $chatSession->getChatSessionId() ));
        return true; 
    }

    /**
     * Updates the recorded last ping time for the given chat session.
     *
     * @param $chatSession  the chat session to update
     * @param $pingTime  (optional) the time to update the last ping to
     */
    public function updateLastPing( ChatSession $chatSession, $pingTime = null )
    {
        if( $pingTime == null )
            $pingTime = time();

        $chatSession->setLastPingUTC( $pingTime );
        
        // Re-cache the modified object
        $this->recache( $chatSession );

        // We deliberately do not update the database to prevent excessive db queries
    }

    /**
     * Returns an array of sessions in the given room that should be considered active.
     * Active sessions are sessions that have recently pinged the server and should be
     * displayed as currently active users.
     *
     * @param $room  the room to get active sessions for
     * @return an array of ChatSession objects reflecting all active chat sessions in
     *         the given room.
     */
    public function getActiveChatSessions( Room $room )
    {
        $sessions = $this->getChatSessions( $room );
        $activeSessions = array();
        foreach( $sessions as $session )
        {
            if( $session->getActive() )
                array_push( $activeSessions, $session );
        }
        return $activeSessions;
    }

    /**
     * Extracts the ChatSession object from the Request
     *
     * @param $request the Request being serviced
     * @param $room the Room the user is connecting to
     * @return the user's ChatSession or null if the user doesn't have a session
     */
    public function extractChatSession(Request $request, Room $room) {
        $session = $request->getSession();

        // Are they already in this chat room?
        if( $session->keyExists( self::CHAT_SESSION_VARIABLE_PREFIX . $room->getRoomId() ) )
        {
            $chatSessionId = $session->get( self::CHAT_SESSION_VARIABLE_PREFIX . $room->getRoomId() );
            return $this->getChatSessionById( $chatSessionId );
        }
        else
        {
            return null;
        }
    }

    /**
     * Updates the last received message of the given chat session to be the given
     * message.
     *
     * @param $chatSession  the chat session to update
     * @param $message  the most recent message for that chat session
     */
    public function updateLastMessage( ChatSession $chatSession, Message $message )
    {
        $chatSession->setLastMessageId( $message->getMessageId() );
        $this->recache( $chatSession );
    }

    /**
     * Caches the given ChatSession object in the main chat session
     * caches.
     *
     * @param $chatSession  a chat session object to cache
     */
    public function recache( ChatSession $chatSession )
    {
        $this->sessionidCache->set( $chatSession->getChatSessionId(), $chatSession, time() + self::CHAT_SESSION_EXPIRE_TIME_DELTA );
    }

    /**
     * This method will recache the chat sessions for a given room.
     *
     * @see ChatSessionCustodian.cleanUp()
     */
    public function setRoomSessions( Room $room, array $sessions )
    {
        $this->perRoomCache->set( $room->getRoomId(), $sessions );
    }

}

