<?php

namespace zc\lib;

/**
 * A class that implements the Builder pattern for Message objects.
 *
 * @author jbowens
 * @since 2012-08-19
 */
class MessageBuilder {

    protected $messageId;
    protected $roomId;
    protected $sentBySessionId;
    protected $username;
    protected $dateSent;
    protected $message;
    protected $isCommand;

    /**
     * Constructs a new Message object with the given values.
     *
     * @return a Message object
     */
    public function build() {
        return new Message( $this );
    }

    // Setters

    public function messageId($messageId) {
        $this->messageId = $messageId;
        return $this;
    }

    public function roomid($roomid) {
        $this->roomid = $roomid;
        return $this;
    }

    public function sentBySessionId($sessionId) {
        $this->sentBySessionId = $sessionId;
        return $this;
    }

    public function username($username) {
        $this->username = $username;
        return $this;
    }

    public function dateSent($dateSent) {
        $this->dateSent = $dateSent;
        return $this;
    }

    public function message($message) {
        $this->message = $message;
        return $this;
    }

    public function isCommand($isCommand) {
        $this->isCommand = $isCommand;
        return $this;
    }

    // Getters

    public function getMessageId() {
        return $this->messageId;
    }

    public function getRoomId() {
        return $this->roomId;
    }

    public function getSentBySessionId() {
        return $this->sentBySessionId;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getDateSentUTC() {
        return $this->dateSent;
    }

    public function getMessage() {
        return $this->message;
    }

    public function getIsCommand() {
        return $this->isCommand;
    }

}
