<?php

namespace zc\lib;

use \esprit\core\db\DatabaseManager;
use \esprit\core\util\Logger;
use \esprit\core\Cache;

/**
 * This class is responsible for cleaning up old chat sessions and removing
 * them from their respective rooms.
 *
 * @author jbowens
 * @since 2012-08-20
 */
class ChatSessionCustodian {

    const CLEAN_UP_PROBABILITY = 100;        // out of 1000
    const LOG_SOURCE = "CUSTODIAN";
    const UPDATE_DB_THRESHOLD = 30;         // seconds
    const REMOVE_FROM_DB_THRESHOLD = 600;   // seconds
    const ACTIVE_THRESHOLD = 10;            // seconds

    const SQL_SELECT_ACTIVE_ROOMS = "SELECT DISTINCT `roomid` FROM `chat_sessions`";
    const SQL_UPDATE_LAST_PING = "UPDATE `chat_sessions` SET `lastPing` = ? WHERE `chatSessionid` = ?";
    const SQL_MARK_INACTIVE = "UPDATE `chat_sessions` SET `active` = 0 WHERE `chatSessionid` = ?"; 
    const SQL_DELETE_SESSION = "DELETE FROM `chat_sessions` WHERE `chatSessionid` = ?";

    protected $dbm;
    protected $cache;
    protected $sessionLastUpdatedCache;
    protected $logger;
    protected $chatSessionSource;
    protected $roomSource;

    public function __construct(DatabaseManager $dbm, Logger $logger, Cache $cache, ChatSessionSource $css = null, RoomSource $rs = null)
    {
        $this->dbm = $dbm;
        $this->logger = $logger;
        $this->cache = $cache;
        if( $css != null )
            $this->chatSessionSource = $css;
        else
            $this->chatSessionSource = new ChatSessionSource($dbm, $logger, $cache);
        if( $rs != null )
            $this->roomSource = $rs;
        else
            $this->roomSource = new RoomSource($dbm, $logger, $cache);
        $this->sessionLastUpdatedCache = $cache->accessNamespace("session_last_updated");
    }

    /**
     * Calls the cleanUp() method with a given probability.
     *
     * @return true iff we cleaned up
     */
    public function cleanUpIfUnlucky() 
    {
        if( rand(1,1000) <= self::CLEAN_UP_PROBABILITY )
        {
            $this->cleanUp();
            return true;
        }
        return false;
    }

    /**
     * Updates any chat sessions that need to be updated. This should
     * be considered an expensive method. It is questionable whether this
     * should ever be called on a user request. If we ever grow, this method
     * will not be scalable.
     */
    public function cleanUp()
    {
        $this->logger->info("Cleaning up chat sessions", self::LOG_SOURCE);

        // Clean up all the rooms that are considered active
        $activeRoomIds = $this->getActiveRoomIds();
        $this->logger->info( count($activeRoomIds) . " active rooms to clean up", self::LOG_SOURCE );

        foreach( $activeRoomIds as $roomId )
        {
            $room = $this->roomSource->getRoomById( $roomId );
            $sessions = $this->chatSessionSource->getChatSessions( $room );

            $stillExistant = array();
            $db = $this->dbm->getDb();
            foreach( $sessions as $session )
            {

                // Try to get the more recent session from the cache
                $moreRecentSession = $this->chatSessionSource->getChatSessionById( $session->getChatSessionId() );
                $session = $moreRecentSession->getLastPingUTC() >= $session->getLastPingUTC() ? $moreRecentSession : $session; 

                $lastUpdated = null;
                if( $this->sessionLastUpdatedCache->isCached( $session->getChatSessionId() ) )
                {
                    $lastUpdated = $this->sessionLastUpdatedCache->get( $session->getChatSessionId() );
                }

                // Update the database's lastPing field if necessary
                if( $lastUpdated == null || ($lastUpdated < (time() - self::UPDATE_DB_THRESHOLD) ) )
                {
                    // We must update the db with the last ping time
                    $stmt = $db->prepare( self::SQL_UPDATE_LAST_PING );
                    $stmt->execute(array( $session->getLastPingUTC(), $session->getChatSessionId() ));
                    // Update the cache
                    $this->sessionLastUpdatedCache->set($session->getChatSessionId(), time());
                }

                // Mark the session as inactive if necessary
                if( ($session->getLastPingUTC() < (time() - self::ACTIVE_THRESHOLD)) && $session->getActive())
                {
                    // This session is no longer active, but we should hold onto it.
                    $stmt = $db->prepare( self::SQL_MARK_INACTIVE );
                    $stmt->execute(array( $session->getChatSessionId() ));
                    $session->setActive( false );
                    $this->chatSessionSource->recache( $session );
                    $this->chatSessionSource->invalidateRoomSessions( $session->getRoomId() );
                    $this->logger->finest( "Marking chat session " . $session->getChatSessionId() . " as inactive", self::LOG_SOURCE);
                }

                // Delete the session if necessary
                if( $session->getLastPingUTC() < (time() - self::REMOVE_FROM_DB_THRESHOLD))
                {
                    // This session should be deleted
                    $stmt = $db->prepare( self::SQL_DELETE_SESSION );
                    $stmt->execute(array( $session->getChatSessionId() ));
                    $this->logger->finest( "Deleting chat session " . $session->getChatSessionId(), self::LOG_SOURCE );
                }
                else
                {
                    // This session is still alive!
                    array_push( $stillExistant, $session );
                }
            }

            $this->chatSessionSource->setRoomSessions( $room, $stillExistant );
        }
    }

    /**
     * Returns an array of room ids that have been used fairly recently. There is
     * not necessary a user still in the rooms returned by this method, but there
     * still exists a chat session object that has not yet been purged.
     *
     * @return array of room ids
     */
    public function getActiveRoomIds()
    {
        $db = $this->dbm->getDb();
        $stmt = $db->query( self::SQL_SELECT_ACTIVE_ROOMS );

        $activeRoomIds = array();
        while( $roomId = $stmt->fetchColumn() )
        {
            array_push($activeRoomIds, $roomId);
        }
        return $activeRoomIds;

    }

}
