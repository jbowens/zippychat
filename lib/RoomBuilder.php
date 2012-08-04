<?php

namespace zc\lib;

/**
 * The Builder pattern for Room objects. If you need to create a new chat room in the database, you should
 * not use this class.
 *
 * @see Room::getBuilder()
 *
 * @author jbowens
 */
class RoomBuilder {

    protected $roomid = null;
    protected $title = null;
    protected $description = null;
    protected $dateCreated = null;
    protected $creatorIpAddress = null;
    protected $passwordHash = null;
    protected $lastGuestNumber = 0;
    protected $lastAccessed = null;

    /* Setters */

    public function roomId( $roomid ) {
        $this->roomid = $roomid;
        return $this;
    }

    public function title( $title ) {
        $this->title = $title;
        return $this;
    }

    public function description( $description ) {
        $this->description = $description;
        return $this;
    }

    public function dateCreated( $dateCreated ) {
        $this->dateCreated = $dateCreated;
        return $this;
    }

    public function creatorIpAddress( $creatorIpAddress ) {
        $this->creatorIpAddress = $creatorIpAddress;
        return $this;
    }

    public function passwordHash( $passwordHash ) {
        $this->passwordHash = $passwordHash;
        return $this;
    }

    public function lastGuestNumber( $lastGuestNumber ) {
        $this->lastGuestNumber = $lastGuestNumber;
        return $this;
    }

    public function lastAccessed( $lastAccessed ) {
        $this->lastAccessed = $lastAccessed;
        return $this;
    }

    /**
     * Constructs a new Room instance with the currently set parameters.
     *
     * @return a Room object reflecting the builder's parameters
     */ 
    public function build() {
        return new Room( $this );
    }

    /* Getters */

    public function getRoomId() {
        return $this->roomid;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getDateCreated() {
        return $this->dateCreated;
    }

    public function getCreatorIpAddress() {
        return $this->creatorIpAddress;
    }

    public function getPasswordHash() {
        return $this->passwordHash;
    }

    public function getLastGuestNumber() {
        return $this->lastGuestNumber;
    }

    public function getLastAccessed() {
        return $this->lastAccessed;
    }

}
