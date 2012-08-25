<?php

namespace zc\lib;

/**
 * A builder class for ChatSession objects.
 *
 * @author jbowens
 */
class ChatSessionBuilder {

    protected $chatSessionId;
    protected $username;
    protected $roomId;
    protected $lastPing;
    protected $loginTime;
    protected $assignedGuestId;
    protected $active = true;

    public function build() {
        return new ChatSession( $this );
    }

    public function getChatSessionId() {
        return $this->chatSessionId;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getRoomId() {
        return $this->roomId;
    }

    public function getLastPing() {
        return $this->lastPing;
    } 

    public function getLoginTime() {
        return $this->loginTime;
    }

    public function getAssignedGuestId() {
        return $this->assignedGuestId;
    }

    public function getActive() {
        return $this->active;
    }

    public function chatSessionId( $chatSessionId ) {
        $this->chatSessionId = $chatSessionId;
        return $this;
    }

    public function username( $username ) {
        $this->username = $username;
        return $this;
    }

    public function roomId( $roomId ) {
        $this->roomId = $roomId;
        return $this;
    }

    public function lastPing( $lastPing ) {
        $this->lastPing = $lastPing;
        return $this;
    }

    public function loginTime( $loginTime ) {
        $this->loginTime = $loginTime;
        return $this;
    }

    public function assignedGuestId( $guestId ) {
        $this->assignedGuestId = $guestId;
        return $this;
    }

    public function active( $active ) {
        $this->active = $active;
        return $this;
    }

}
