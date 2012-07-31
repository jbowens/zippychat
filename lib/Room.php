<?php

namespace zc\lib;

/**
 * Represents a chat room.
 *
 * @author jbowens
 */
class Room {

    protected $roomid;
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
        return self::getBuilder()->roomid( $a['roomid'] )
                                 ->title( $a['title'] )
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
        $this->roomid = $rb->getRoomid();
        $this->title = $rb->getTitle();
        $this->description = $rb->getDescription();
        $this->dateCreated = $rb->getDateCreated();
        $this->creatorIpAddress = $rb->getCreatorIpAddress();
        $this->passwordHash = $rb->getPasswordHash();
        $this->lastGuestNumber = $rb->getLastGuestNumber();
        $this->lastAccessed = $db->getLastAccessed();
    }

    public function getRoomId() {
        return $this->roomid;
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
        return $this->passowrdHash;
    }

    public function getLastGuestNumber() {
        return $this->lastGuestNumber;
    }

    public function getLastAccessedRaw() {
        return $this->lastAccessed;
    }

}
