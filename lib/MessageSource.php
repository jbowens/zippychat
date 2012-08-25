<?php

namespace zc\lib;

use \esprit\core\db\DatabaseManager;
use \esprit\core\util\Logger;
use \esprit\core\Cache;

/**
 * A class useful for retrieving Message objects. Whenever you need to get
 * messages from the database, you should likely be using this class.
 *
 * @author jbowens
 * @since 2012-08-19
 */
class MessageSource {

    const MESSAGES_CACHE_NAMESPACE = "messages";
    const CACHE_INVALIDATION_TIME = 60;
    const OLD_MESSAGE_TIME = 60;
    const DEFAULT_MESSAGES_TO_LOAD = 50;
    const LOG_SOURCE = "MessageSource";
    const SQL_GET_MESSAGES_SINCE_MESSAGEID = "SELECT * FROM `messages` WHERE `roomid` = ? AND `messageid` > ?";
    const SQL_GET_MOST_RECENT_MESSAGES = "SELECT * FROM `messages` WHERE `roomid` = ? ORDER BY `messageid` DESC LIMIT ";
    const SQL_GET_MESSAGE_BY_ID = "SELECT * FROM `messages` WHERE `messageid` = ?";

    protected $dbm;
    protected $logger;

    // Maps roomids to arrays of recent messages
    protected $messageCache;

    public function __construct(DatabaseManager $dbm, Logger $logger, Cache $cache)
    {
        $this->dbm = $dbm;
        $this->logger = $logger;
        $this->messageCache = $cache->accessNamespace( self::MESSAGES_CACHE_NAMESPACE );
    }

    /**
     * Gets a message from the database by id. Warning: This method always hits the
     * database and so is NOT cheap.
     *
     * @param $messageid  the message id to lookup
     * @return the corresponding Message object, or null if the message doesn't exist
     */
    public function getMessageById($messageId)
    {
        // This method should be called really infrequently, so it's not worth caching.
        $this->logger->info("Grabbing message " . $messageId . " from the database", self::LOG_SOURCE);

        $db = $this->dbm->getDb();
        $pstmt = $db->prepare( self::SQL_GET_MESSAGE_BY_ID );
        $pstmt->execute(array($messageId));
        $messageArray = $pstmt->fetch( \PDO::FETCH_ASSOC );
        
        if( $messageArray == null )
            return null;

        return Message::createFromArray( $messageArray );
    }

    /**
     * Retrieves all messages in the given room since the given
     * messageid. 
     *
     * @param $room  a Room object
     * @param $messageid  a messageid
     * @return a list of Message objects representing all messages
     * in the room since the given messageid
     */
    public function getMessagesSinceMessageId( Room $room, $messageId )
    {
        if( $this->messageCache->isCached( $room->getRoomId() ) )
        {
            // Cache hit
            // We're making the assumption here that the cache is not out of
            // date. That means when caching we need to set short invalidation
            // times and whenever we update the data, we need to manually
            // invalidate the cache.
            $allMessages = $this->messageCache->get( $room->getRoomId() );
            $youthfulCutoff = time() - self::OLD_MESSAGE_TIME;
            $relevantMessages = array();
            $freshMessages = array();
            foreach( $allMessages as $message )
            {
                if( $message->getMessageId() > $messageId )
                {
                    array_push($relevantMessages, $message);
                }
                if( $message->getDateSentUTC() >= $youthfulCutoff )
                {
                    array_push($freshMessages, $message);
                }
            }

            // Recache the list if the list of young messages is shorter than the list of
            // cached messages
            if( count($freshMessages) < count($allMessages) )
            {
                $this->logger->info("Trimming message cache for room " . $room->getRoomId(), self::LOG_SOURCE);
                $this->messageCache->set( $room->getRoomId(), $freshMessages, self::CACHE_INVALIDATION_TIME );
            }

            return $relevantMessages;
        }
        else
        {
            // Cache miss, hit the database
            $this->logger->info("Cache miss on messages for room " . $room->getRoomId(), self::LOG_SOURCE);
            $recentMessages = $this->getMostRecentMessages( $room, self::DEFAULT_MESSAGES_TO_LOAD );

            // Cache it
            $this->messageCache->set( $room->getRoomId(), $messages, self::CACHE_INVALIDATION_TIME );
            
            $messages = array();
            foreach( $recentMessages as $message )
            {
                if( $message->getMessageId() > $messageid )
                {
                    array_push($messages, $message);
                } 
            }

            return $messages;
        }
    }

    /**
     * Retrieves messages since the given time where the given time is
     * recent. This method will use the cache. It's possible for messages
     * to have been removed from the cache since the given time.
     * 
     * @param $room
     * @param $time
     * @return an array of Message objects
     */
    public function getMessagesSinceTime( Room $room, $time )
    {
        if( $this->messageCache->isCached( $room->getRoomId() ) )
        {
            // Cache hit
            $recentMessages = $this->messageCache->get( $room->getRoomId() );
        }
        else
        {
            // Cache miss
            $this->logger->info( "Cache miss on request for messages since give time in room " . $room->getRoomId(), self::LOG_SOURCE );
            // Make the database call
            $recentMessaages = $this->getMostRecentMessages( $room, self::DEFAULT_MESSAGES_TO_LOAD );
            $this->messageCache->set( $room->getRoomId(), $recentMessages, self::CACHE_INVALIDATION_TIME );
        }

        $messages = array();
        foreach( $recentMessages as $message )
        {
            if( $message->getLastPingUTC() >= $time )
            {
                array_push($messages, $message);
            }
        }
        return $messages;
    }

    /**
     * Retrieves the most recent N messages from the given room. This method
     * always results in a database call.
     *
     * @param $room  the room to retrieve the messages from
     * @param $howMany  the number of messages to return
     * @return an array of Message objects
     */
    public function getMostRecentMessages( Room $room, $howMany )
    {
        // Log a warning if they ask for 0 or if $howMany is NaN
        if( intVal($howMany) == 0 )
        {
            $this->logger->warning("Request for 0 of the most recent messages in a room", self::LOG_SOURCE);
            return array();
        }
        else
        {
            $this->logger->info("Grabbing most recent " . $howMany . " messages for room " . $room->getRoomId(), self::LOG_SOURCE);
            $db = $this->dbm->getDb();
            // Append $howMany to complete the LIMIT statement
            $pstmt = $db->prepare( self::SQL_GET_MOST_RECENT_MESSAGES . intVal($howMany) );
            $pstmt->execute(array( $room->getRoomId() ));

            $messages = array();
            while( $messageArray = $pstmt->fetch(\PDO::FETCH_ASSOC) )
            {
                $message = Message::createFromArray( $messageArray );
                array_push( $messages, $message );
            }

            return $messages;
        }
    }

    /**
     * Sorts an array of messages chronologically. 
     *
     * @param an array of Message object
     */
    public static function sortChronologically( array $messages )
    {
        // Fuck yeah anonymous functions
        usort( $messages, function(Message $a, Message $b) {
            return $a->getMessageId() - $b->getMessageId();
        });
        return $messages; 
    }

}
