<?php

namespace zc\lib;

/**
 * Represents a chatter in a specific chat room. Feel free to add more setters
 * as you deem necessary, but remember that this is a very frequently cached
 * object. If you're going to update values for chat session, you need to update
 * all of the relevant caches or risk stale data.
 *
 * @author jbowens
 */
class ChatSession
{

    protected $chatSessionId;
    protected $username;
    protected $roomId;
    protected $lastPing;
    protected $loginTime;
    protected $assignedGuestId;
    protected $active;

    public static function getBuilder() {
        return new ChatSessionBuilder();
    }

    public static function createFromArray( array $chatSessionData )
    {
        return self::getBuilder()->chatSessionId( $chatSessionData['chatSessionid'] )
                                 ->username( $chatSessionData['username'] )
                                 ->roomId( $chatSessionData['roomid'] )
                                 ->lastPing(  $chatSessionData['lastPing'] )
                                 ->loginTime( $chatSessionData['loginTime'] )
                                 ->assignedGuestId( $chatSessionData['assignedGuestId'] )
                                 ->active( $chatSessionData['active'] )
                                 ->build();
    }

    public function __construct( ChatSessionBuilder $csB )
    {
        $this->chatSessionId = $csB->getChatSessionId();
        $this->username = $csB->getUsername();
        $this->roomId = $csB->getRoomId();
        $this->lastPing = $csB->getLastPing();
        $this->loginTime = $csB->getLoginTime();
        $this->assignedGuestId = $csB->getAssignedGuestId();
        $this->active = $csB->getActive();
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

    public function getLastPingUTC() {
        return $this->lastPing;
    } 

    public function getLoginTimeUTC() {
        return $this->loginTime;
    }

    public function getAssignedGuestId() {
        return $this->assignedGuestId;
    }

    public function getActive() {
        return $this->active;
    }

    public function setLastPingUTC( $newLastPingUTC ) {
        $this->lastPing = $newLastPingUTC;
    }

    public function setActive( $isActive ) {
        $this->active = $isActive;
    }

    public function toArray() {
        return array(
            'chatSessionId' => (int) $this->getChatSessionId(),
            'username' => $this->getUsername(),
            'active' => (boolean) $this->getActive(),
            'roomId' => (int) $this->getRoomId(),
            'loginTime' => (int) $this->getLoginTimeUTC(),
            'lastPing' => (int) $this->getLastPingUTC()
        );
    }

}
