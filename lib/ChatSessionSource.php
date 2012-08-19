<?php

namespace zc\lib;

use \esprit\core\db\DatabaseManager;
use \esprit\core\Cache;
use \esprit\core\util\Logger;

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
    const BAD_ID_EXPIRE_TIME_DELTA = 30;
    const CHAT_SESSION_EXPIRE_TIME_DELTA = 20;
    const SQL_CHAT_SESSION_BY_ID = "SELECT * FROM `chat_sessions` WHERE `chatSessionid` = ?";
    const SQL_CREATE_CHAT_SESSION = "INSERT INTO `chat_sessions` (`roomid`, `username`, `lastPing`, `loginTime`, `assignedGuestId`) VALUES(?, ?, ?, ?, ?)";
    const SQL_SELECT_LAST_GUEST_NUMBER = "SELECT `lastGuestNumber` FROM `rooms` WHERE `roomid` = ?";
    const SQL_UPDATE_LAST_GUEST_NUMBER = "UPDATE `rooms` SET `lastGuestNumber` = ? WHERE `roomid` = ?";

    protected $dbm;
    protected $logger;
    protected $sessionidCache;

    public function __construct( DatabaseManager $dbm, Logger $logger, Cache $cache )
    {
        $this->dbm = $dbm;
        $this->sessionidCache = $cache->accessNamespace( self::SESSIONID_CACHE_NAMESPACE );
    }

    /**
     * Retrieves a chat session by its chat session id. This id is usually stored
     * in session data.
     *
     * @param $chatSessionid  the id of the chat session
     * @return a ChatSession object corresponding to the given id
     */
    public function getChatSessionById( $chatSessionid )
    {
        if( $this->sessionidCache->isCached( $chatSessionid ) )
        {
            // Cache hit
            return $this->sessionidCache->get( $chatSessionid );
        }
        else
        {
            // Cache miss
            $db = $this->dbm->getDb();
            $pstmt = $db->prepare( self::SQL_CHAT_SESSION_BY_ID );
            $pstmt->execute( array( $chatSessionid ) );
            $sessionAssoc = $pstmt->fetch( \PDO::FETCH_ASSOC );

            if( $sessionAssoc == null )
            {
                // This session doesn't exist!
                $this->logger->warning( "Chat session id ".$chatSessionid." was queried, but it doesn't exist", self::LOG_SOURCE );
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
        $pstmt->execute(array( $room->getRoomId(),
                               $username,
                               time(),
                               time(),
                               $guestUserId ));
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
     * Caches the given ChatSession object in the main chat session
     * caches.
     *
     * @param $chatSession  a chat session object to cache
     */
    protected function recache( ChatSession $chatSession )
    {
        $this->sessionidCache->set( $chatSession->getChatSessionid(), $chatSession, time() + self::CHAT_SESSION_EXPIRE_TIME_DELTA );
    }

}

