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
    const USERNAME_CHANGES_CACHE_NAMESPACE = "username_changes";
    const BAD_ID_EXPIRE_TIME_DELTA = 30;
    const CHAT_SESSION_EXPIRE_TIME_DELTA = 20;
    const USERNAME_CHANGE_EXPIRE_TIME_DELTA = 600;
    const CHAT_SESSION_VARIABLE_PREFIX = "chatsession_";
    
    const SQL_CHAT_SESSION_BY_ID = "SELECT * FROM `chat_sessions` WHERE `chatSessionid` = ?";
    const SQL_CREATE_CHAT_SESSION = "INSERT INTO `chat_sessions` (`roomid`, `username`, `lastPing`, `loginTime`, `assignedGuestId`) VALUES(?, ?, ?, ?, ?)";
    const SQL_SELECT_LAST_GUEST_NUMBER = "SELECT `lastGuestNumber` FROM `rooms` WHERE `roomid` = ?";
    const SQL_UPDATE_LAST_GUEST_NUMBER = "UPDATE `rooms` SET `lastGuestNumber` = ? WHERE `roomid` = ?";
    const SQL_GET_ALL_SESSIONS = "SELECT * FROM `chat_sessions` WHERE `roomid` = ?";
    const SQL_REACTIVE_CHAT_SESSION = "UPDATE `chat_sessions` SET `active` = 1 WHERE `chatSessionid` = ?";
    const SQL_UPDATE_USERNAME = "UPDATE `chat_sessions` SET `username` = ? WHERE `chatSessionid` = ?";
    const SQL_IS_USERNAME_IN_USE = "SELECT count(`chatSessionid`) FROM `chat_sessions` WHERE `roomid` = ? AND `username` = ?";
    const SQL_INSERT_USERNAME_CHANGE = "INSERT INTO `username_changes` (`roomid`,`chatSessionid`,`newUsername`) VALUES(?,?,?)";
    const SQL_GET_RECENT_USERNAME_CHANGES = "SELECT * FROM username_changes WHERE `roomid` = ? ORDER BY changeid DESC LIMIT 5";
    const SQL_GET_GREATEST_USERNAME_CHANGEID = "SELECT max(changeid) FROM username_changes";

    protected $dbm;
    protected $logger;
    protected $sessionidCache;
    protected $perRoomCache;
    protected $usernameChangesCache;

    public function __construct( DatabaseManager $dbm, Logger $logger, Cache $cache )
    {
        $this->dbm = $dbm;
        $this->logger = $logger;
        $this->sessionidCache = $cache->accessNamespace( self::SESSIONID_CACHE_NAMESPACE );
        $this->perRoomCache = $cache->accessNamespace( self::PER_ROOM_CACHE_NAMESPACE );
        $this->usernameChangesCache = $cache->accessNamespace( self::USERNAME_CHANGES_CACHE_NAMESPACE );
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
        // Invalidate the per-room cache
        if( $this->perRoomCache->isCached( $room->getRoomId() ) )
        {
            $this->perRoomCache->delete( $room->getRoomId() );
        }
        
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
        $this->updateLastPing( $chatSession );
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
     * Changes the given chat session's username to the given string. 
     * 
     * @param $chatSession  the chat session to modify
     * @param $newUsername  the new username to use
     * @return true iff the change succeeded. false usually indicates an illegal;
     *         requested username
     */
    public function changeUsername( ChatSession $chatSession, $newUsername )
    {
        // We use a transaction here to make sure that no users ever have the same username
        // in the same room.
        $db = $this->dbm->getDb();
        $db->beginTransaction();

        if( ! $this->validateUsername( $chatSession, $newUsername ) )
        {
            $db->rollBack();
            return false;
        }

        $this->logger->info("Changing username to " . $newUsername, self::LOG_SOURCE);

        // Update the sessionid cache
        $chatSession->setUsername( $newUsername );
        $this->recache( $chatSession );

        // Update the database
        $stmt = $db->prepare( self::SQL_UPDATE_USERNAME );
        $stmt->execute(array( $newUsername, $chatSession->getChatSessionId() ));
        $db->commit();

        // Invalidate the current per-room cache
        $this->perRoomCache->delete( $chatSession->getRoomId() );

        $stmt = $db->prepare( self::SQL_INSERT_USERNAME_CHANGE );
        $stmt->execute(array(
            $chatSession->getRoomId(),
            $chatSession->getChatSessionId(),
            $newUsername
        ));
        
        // Invalidate the cache now
        $this->usernameChangesCache->delete( $chatSession->getRoomId() );

        return true;
    }

    /**
     * Retrieves recent username changes in the given room.
     *
     * @param $room  the room to get changes for
     * @param $lastChangeId  the last known about change id
     */
    public function getUsernameChanges(Room $room, $lastChangeId)
    {
        if( $this->usernameChangesCache->isCached( $room->getRoomId() ) )
        {
            // Cache hit
            $recentChanges = $this->usernameChangesCache->get( $room->getRoomId() );
            $relevantChanges = array();
            foreach( $recentChanges as $change )
            {
                $change['changeid'] = (int) $change['changeid'];
                $change['roomid'] = (int) $change['roomid'];
                $change['chatSessionid'] = (int) $change['chatSessionid'];
                if( $change['changeid'] > $lastChangeId )
                    array_push($relevantChanges, $change);
            }
            return $relevantChanges;
        } else {
            // Cache miss
            $this->logger->info("Cache miss for username changes for room " . $room->getRoomId(), self::LOG_SOURCE);
            $db = $this->dbm->getDb();
            $stmt = $db->prepare(self::SQL_GET_RECENT_USERNAME_CHANGES);
            $stmt->execute(array( $room->getRoomId() ));
            $recentChanges = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $relevantChanges = array();
            foreach( $recentChanges as $change )
            {
                $change['changeid'] = (int) $change['changeid'];
                $change['roomid'] = (int) $change['roomid'];
                $change['chatSessionid'] = (int) $change['chatSessionid'];
                if( $change['changeid'] > $lastChangeId )
                    array_push($relevantChanges, $change);
            }

            // Save to the cache
            $this->usernameChangesCache->set( $room->getRoomId(), $recentChanges, self::USERNAME_CHANGE_EXPIRE_TIME_DELTA );

            return $relevantChanges;
        }
    }

    /**
     * Gets the most recent username change id from the database. This will
     * return -1 if there are no recent username changes in the datbase.
     */
    public function getMostRecentUernameChangeId() {
        $db = $this->dbm->getDb();
        $stmt = $db->query( self::SQL_GET_GREATEST_USERNAME_CHANGEID );
        $result = $stmt->fetchColumn();
        if( $result === false || $result == null )
            return -1;
        else
            return $result;
    }

    /**
     * Determines if the given username is a valid username for a user who
     * is changing their default username to the given name. This method
     * requires the requesting chat session because username validity can be
     * room-specific (if the username is already taken) or user-specific (if
     * the user is trying to switch back to their original assigned Guest#
     * username).
     *
     * @param $chatSession  the chat session requesting the change
     * @param $potentialUsername  the requested username
     * @return true iff the username is valid
     */
    public function validateUsername( $chatSession, $potentialUsername )
    {
        // Don't allow them to appear to have power when they don't
        if( strtolower($potentialUsername) == "admin" || strtolower($potentialUsername) == "moderator" )
            return false; 

        // Don't allow them to fake other users or take up usernames in the Guest namespace
        if( substr($potentialUsername, 0, 5) == 'Guest' )
            return (int) (substr($potentialUsername, 5)) == $chatSession->getAssignedGuestId();

        // Let's check this one's not taken in the database
        $db = $this->dbm->getDb();
        $stmt = $db->prepare( self::SQL_IS_USERNAME_IN_USE );
        $stmt->execute(array( $chatSession->getRoomId(), $potentialUsername ));
        $inUse = $stmt->fetchColumn();

        return ! $inUse;
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

