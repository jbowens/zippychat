<?php

namespace zc\lib;

/**
 * Represents a chat room.
 *
 * @author jbowens
 */
class Room {

    protected $roomId;
    protected $title;
    protected $description;
    protected $dateCreated;
    protected $creatorIpAddress;
    protected $passwordHash;
    protected $lastGuestNumber;
    protected $lastAccessed;

    public static function getBuilder() {
        return new RoomBuilder();
    }

    /**
     * Constructs a Room object from an associative array of the database fields.
     *
     * @param $a  an array, usually retrieved from PDOStatement::fetch(PDO::FETCH_ASSOC)
     * @return  a Room object
     */
    public static function createFromArray( array $a )
    {
        return self::getBuilder()->roomId( $a['roomid'] )
                                 ->title( $a['title'] )
                                 ->description( $a['description'] )
                                 ->dateCreated( $a['dateCreated'] )
                                 ->creatorIpAddress( $a['creatorIp'] )
                                 ->passwordHash( $a['password'] )
                                 ->lastGuestNumber( $a['lastGuestNumber'] )
                                 ->lastAccessed( $a['lastAccessed'] )
                                 ->build();
    }

    /**
     * @see RoomBuilder.build()
     */
    public function __construct( RoomBuilder $rb ) {
        $this->roomId = $rb->getRoomId();
        $this->title = $rb->getTitle();
        $this->description = $rb->getDescription();
        $this->dateCreated = $rb->getDateCreated();
        $this->creatorIpAddress = $rb->getCreatorIpAddress();
        $this->passwordHash = $rb->getPasswordHash();
        $this->lastGuestNumber = $rb->getLastGuestNumber();
        $this->lastAccessed = $rb->getLastAccessed();
    }

    /**
     * Property getters
     */

    public function getRoomId() {
        return $this->roomId;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getDateCreatedRaw() {
        return $this->dateCreated;
    }

    public function getCreatorIpAddress() {
        return $this->creatorIpAddress;
    }

    public function getPasswordHash() {
        return $this->passwordHash;
    }

    public function isPasswordProtected() {
        return ($this->passwordHash != null);
    }

    public function getLastGuestNumber() {
        return $this->lastGuestNumber;
    }

    public function getLastAccessedRaw() {
        return $this->lastAccessed;
    }

    /**
     * Logic methods
     */
    
    public function getUrlIdentifier() {

        // Maintain backwards compatability with rooms created when we used the roomid
        // as the url identifier.
        if( $this->getRoomId() < 32880 )
            return $this->getRoomId();

        $num = $this->roomId * 13 + 17;
        
        $base26 = base_convert($num, 10, 26);
        
        $mapping = array('0' => 'z', '1' => 'y', '2' => 'x', '3' => 'w', '4' => 'v', '5' => 'u', '6' => 't', '7' => 's', '8' => 'r', '9' => 'q');
        $key = $base26;
        
        foreach( $mapping as $k => $v ) {
            $key = str_ireplace($k, $v, $key);
        }
        
        return $key;
    }
}
