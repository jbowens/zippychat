<?php

namespace zc\lib;

/**
 * Represents a chatter in a specific chat room.
 *
 * @author jbowens
 */
class User
{

    protected $userid;
    protected $username;
    protected $roomid;
    protected $lastPing;
    protected $loginTime;

    public function __construct( UserBuilder $uB )
    {
        $this->userid = $uB->getUserid();
        $this->username = $uB->getUsername();
        $this->roomid = $uB->getRoomid();
        $this->lastPing = $uB->getLastPing();
        $this->loginTime = $uB->getLoginTime();
    }

    public function getUserid() {
        return $this->userid;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getRoomid() {
        return $this->roomid;
    }

    public function getLastPingUTC() {
        return $this->lastPing;
    } 

    public function getLoginTimeUTC() {
        return $this->loginTime;
    }

}
