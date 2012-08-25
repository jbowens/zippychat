<?php

namespace zc\lib;

/**
 * Represents a chat message in one of the chat rooms.
 *
 * @author jbowens
 * @since 2012-08-19
 */
class Message {

    protected $messageId;
    protected $roomId;
    protected $sentBySessionId;
    protected $username;
    protected $dateSent;
    protected $message;
    protected $isCommand;

    /**
     * Creates a Message object from an associative database
     * row array.
     *
     * @param $a an associative array with the database fields as keys
     * @return a Message object
     */
    public static function createFromArray( array $a )
    {
        return self::getBuilder()->messageId( $a['messageid'] )
                                 ->roomId( $a['roomid'] )
                                 ->sentBySessionId( $a['sentBySessionid'] )
                                 ->username( $a['username'] )
                                 ->dateSent( $a['dateSent'] )
                                 ->message( $a['message'] )
                                 ->isCommand( $a['isCommand'] )
                                 ->build();
    } 


    /**
     * Get a MessageBuilder object.
     */
    public static function getBuilder() {
        return new MessageBuilder();
    }

    /**
     * To create a Message object you should be calling
     * MessageBuilder.build()
     */
    public function __construct(MessageBuilder $builder) {
        $this->messageId = $builder->getMessageId();
        $this->roomId = $builder->getRoomId();
        $this->sentBySessionId = $builder->getSentBySessionId();
        $this->username = $builder->getUsername();
        $this->dateSent = $builder->getDateSent();
        $this->message = $builder->getMessage();
        $this->isCommand = $builder->getIsCommand();
    }

    /* Getters */
    
    public function getMessageId() {
        return $this->messageId;
    }

    public functon getRoomId() {
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
