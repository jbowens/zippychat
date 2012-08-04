<?php

namespace zc\lib;

/**
 * A builder class for User objects.
 *
 * @author jbowens
 */
class UserBuilder {

    protected $userid;
    protected $username;
    protected $roomid;
    protected $lastPing;
    protected $loginTime;

    public function getUserid() {
        return $this->userid;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getRoomid() {
        return $this->roomid;
    }

    public function getLastPing() {
        return $this->lastPing;
    } 

    public function getLoginTime() {
        return $this->loginTime;
    }

    public function userid( $userid ) {
        $this->userid = $userid;
        return $this;
    }

    public function username( $username ) {
        $this->username = $username;
        return $this;
    }

    public function roomid( $roomid ) {
        $this->roomid = $roomid;
        return $this;
    }

    public function lastPing( $lastPing ) {
        $this->lastPing = $lastPing;
    }

    public function loginTime( $loginTime ) {
        $this->loginTime = $loginTime;
    }

}
