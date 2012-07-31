<?php

namespace zc\lib;

use \esprit\core\db\DatabaseManager;
use \esprit\core\Cache;
use \esprit\core\util\OptionalInstance;
use \esprit\core\Time;

/**
 * If you need a Room instance, you most likely can retrieve the relevant Room
 * through this class. It is capable of querying the database for rooms and 
 * caching Rooms, usually in Memcached.
 *
 * @author jbowens
 */
class RoomSource {

    const ROOM_CACHE_NAMESPACE = '\zc\rooms';
    const SQL_GET_ROOM_BY_ROOMID = "SELECT * FROM `rooms` WHERE `roomid` = ?"; 

    /* How long should rooms stay in the cache? */
    protected static $CACHE_EXPIRATION = TIME::A_WEEK;

    protected $dbm;
    protected $cache;

    /**
     * Constructs a new RoomSource
     *
     * @param $dbm  a DatabaseManager object
     * @param $cache  a Cache
     */
    public function __construct( DatabaseManager $dbm, Cache $cache )
    {
        $this->dbm = $dbm;
        $this->cache = $cache->accessNamespace( self::ROOM_CACHE_NAMESPACE );
    }

    /**
     * Finds a room by its roomid.
     *
     * @param $roomid  the id of the room
     * @return the corresponding Room object, or null if no room has the given room id.
     */
    public function getRoomById( $roomid )
    {
        $cacheResult = $this->cache->get( $roomid );
        if( $cacheResult == null )
        {
            // Cache miss
            $db = $this->dbm->getDb();
            $getRoomStmt = $db->prepare( self::SQL_GET_ROOM_BY_ROOMID );
            $getRoomStmt->execute(array( $roomid ));
            $roomAssocArray = $getRoomStmt->fetch(PDO::FETCH_ASSOC);
            $room = Room::createFromArray( $roomAssocArray );

            // Store the object in the cache for future reference
            $this->cache( $room );

            // Return the room
            return $room;
        }
        else
        {
            // Cache hit
            $room = $cacheResult->get();
            return $room;
        }
    }

    /**
     * Stores a Room object in the cache.
     *
     * @param $room  the Room object to cache
     */
    protected function cache( Room $room )
    {
        $this->cache->set( $room->getRoomId(), new OptionalInstance( $room ), self::$CACHE_EXPIRATION );
    }

}
