<?php

namespace zc\lib;

/**
 * Represents a chatter in a specific chat room.
 *
 * @author jbowens
 */
class ChatSession
{

    protected $chatSessionid;
    protected $username;
    protected $roomid;
    protected $lastPing;
    protected $loginTime;

    public function __construct( ChatSessionBuilder $csB )
    {
        $this->chatSessionid = $csB->getChatSessionid();
        $this->username = $csB->getUsername();
        $this->roomid = $csB->getRoomid();
        $this->lastPing = $csB->getLastPing();
        $this->loginTime = $csB->getLoginTime();
    }

    public function getChatSessionid() {
        return $this->chatSessionid;
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

    public function setLastPingUTC( $newLastPingUTC ) {
        $this->lastPing = $newLastPingUTC;
    }

}
