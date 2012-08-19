<?php

namespace zc\lib;

use \PDO;

use \esprit\core\db\DatabaseManager;
use \esprit\core\util\Logger;
use \esprit\core\Cache;
use \esprit\core\Time;
use \esprit\core\security\SaltedHashingStrategy;

/**
 * If you need a Room instance, you most likely can retrieve the relevant Room
 * through this class. It is capable of querying the database for rooms and 
 * caching Rooms, usually in Memcached.
 *
 * @author jbowens
 */
class RoomSource {

    const ROOM_CACHE_NAMESPACE = '\zc\rooms';

    const HASHING_SALT = "H2^sA@5m.aZ3";
    const HASHING_ALGORITHM = "sha256";

    const SQL_CREATE_ROOM = "INSERT INTO `rooms` (title,description,dateCreated,creatorIp,password,lastGuestNumber,lastAccessed) VALUES(?,?,?,?,?,?,?)";
    const SQL_GET_ROOM_BY_ROOMID = "SELECT * FROM `rooms` WHERE `roomid` = ?"; 

    /* How long should rooms stay in the cache? */
    protected static $CACHE_EXPIRATION = TIME::A_WEEK;

    protected $dbm;
    protected $logger;
    protected $cache;

    /**
     * Constructs a new RoomSource
     *
     * @param $dbm  a DatabaseManager object
     * @param $cache  a Cache
     */
    public function __construct( DatabaseManager $dbm, Logger $logger, Cache $cache )
    {
        $this->dbm = $dbm;
        $this->logger = $logger;
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
        if( ! $this->cache->isCached( $roomid ) )
        {
            // Cache miss
            $db = $this->dbm->getDb();
            $getRoomStmt = $db->prepare( self::SQL_GET_ROOM_BY_ROOMID );
            $getRoomStmt->execute(array( $roomid ));
            $roomAssocArray = $getRoomStmt->fetch(PDO::FETCH_ASSOC);
            
            if( $roomAssocArray == null )
                return null;

            $room = Room::createFromArray( $roomAssocArray );

            // Store the object in the cache for future reference
            $this->cache( $room );

            // Return the room
            return $room;
        }
        else
        {
            // Cache hit
            $room = $this->cache->get( $roomid );
            return $room;
        }
    }

    /**
     * Finds a room by its identifier. The identifier is the one used in urls and
     * the one returns by Room.getUrlIdentifier().
     *
     * @param $identifier  the unique string room identifier for the room
     * @return the corresponding Room object 
     */
    public function getRoomByIdentifier( $identifier )
    {
        if( is_numeric($identifier) && $identifier < 32880 )
            $roomid = (int) $identifier;
        else
        {
            $mapping = array('z' => '0', 'y' => '1', 'x' => '2', 'w' => '3', 'v' => '4', 'u' => '5', 't' => '6', 's' => '7', 'r' => '8', 'q' => '9');
            $key = $identifier;
            
            foreach( $mapping as $k => $v ) {
                $key = str_ireplace($k, $v, $key);
            }

            $roomid = base_convert($key, 26, 10);
        }

        return $this->getRoomById( $roomid );
    }

    /**
     * Creates a Room with the given details and inserts it into the
     * database. 
     *
     * @param $roomBuilder  a RoomBuilder object with the data for the Room
     * @return the newly created Room object
     */
    public function createRoom( RoomBuilder $rB )
    {
        $db = $this->dbm->getDb();

        if( $rB->getDateCreated() == null )
            $rB->dateCreated( time() );

        $insertStmt = $db->prepare( self::SQL_CREATE_ROOM );
        $insertStmt->execute(Array(
                $rB->getTitle(),
                $rB->getDescription(),
                $rB->getDateCreated(),
                ip2long( $rB->getCreatorIpAddress() ),
                $rB->getPasswordHash(),
                $rB->getLastGuestNumber(),
                $rB->getLastAccessed()
            ));

        $room = $rB->roomid( $db->lastInsertId() )->build();
        $this->cache( $room );

        return $room;
    }

    /**
     * Retrieves the hashing algorithm used for hashing room passwords.
     *
     * @return a PasswordHashingStrategy object
     */
    public static function getPasswordHasher()
    {
        return new SaltedHashingStrategy( self::HASHING_ALGORITHM, self::HASHING_SALT );
    }

    /**
     * Stores a Room object in the cache.
     *
     * @param $room  the Room object to cache
     */
    protected function cache( Room $room )
    {
        $this->cache->set( $room->getRoomId(), $room, self::$CACHE_EXPIRATION );
    }

}
