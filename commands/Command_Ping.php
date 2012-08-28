<?php

namespace zc\commands;

use \esprit\core\Request;
use \esprit\core\Response;
use \esprit\core\exceptions\PageNotFoundException;

use \zc\lib\BaseCommand;
use \zc\lib\ChatSession;
use \zc\lib\ChatSessionSource;
use \zc\lib\MessageSource;
use \zc\lib\Room;
use \zc\lib\RoomAware;

/**
 * Command responsible for updating chat users with fresh chat room data.
 *
 * @author jbowens
 */
class Command_Ping extends BaseCommand
{
    use RoomAware;

    const COMMAND_NAME = "Ping";
    const MESSAGES_TIME_CUTOFF = 60;

    public function getName() {
        return self::COMMAND_NAME;
    }

    public function generateResponse(Request $request, Response $response) {

        // Identify the requested room
        $room = $this->getRequestedRoom($request);
        if( $room == null )
        {
            throw new PageNotFoundException(); 
        }

        // Identify the user's chat session
        $chatSessionSource = $this->getChatSessionSource();
        $chatSession = $chatSessionSource->extractChatSession($request, $room);
        if( $chatSession == null )
        {
            // Not sure how this is possible, but no harm in creating a session for them
            $this->logger->info("The user does not have an existing chat session", $this->getName());
            $response->set('noSession', true);
        }
        else if( ! $chatSession->getActive() )
        {
            // We marked them as inactive. Let's reactivate now that we have a recent request from them
            $this->logger->info("Reactivating user chat session", $this->getName());
            $chatSessionSource->reactivateChatSession( $chatSession );
        }
        $response->set('chatSession', $chatSession);

        if( $chatSession != null )
        {
            $messages = $this->getNewMessages( $chatSession, $room, $request );
            $response->set('messages', $messages);
        }

        return $response;
    }

    /**
     * Retrieves any new messages for the given chat session.
     * 
     * @param $chatSession  the chat session to retrieve messages for
     * @param $room  the room to get messages for
     * @return an array of Message objects
     */
    public function getNewMessages( ChatSession $chatSession, Room $room, Request $request )
    {
        if( $chatSession == null )
        {
            $this->logger->warning("Request for new messages for a null chatSession", $this->getName());
            return array();
        }

        $messageSource = $this->getMessageSource();
        $chatSessionSource = $this->getChatSessionSource();

        if( $request->getParamExists( 'lastMsgId' ) )
        {
            $lastMessageId = $request->getGet( 'lastMsgId' );
            $newMessages = MessageSource::sortChronologically( $messageSource->getMessagesSinceMessageId( $room, $lastMessageId ) );
        }
        else if( $request->getParamExists( 'fromTime' ) )
        {
            // Cap cutoff time 
            $fromTime = $request->getGet( 'fromTime' );
            if( $fromTime < time() - self::MESSAGES_TIME_CUTOFF )
            {
                $fromTime = time() - self::MESSAGES_TIME_CUTOFF;
            }
            $newMessages = $messageSource->getMessagesSinceTime( $room, $fromTime );
        }
        else
        {
            // Malformed ping
            $this->logger->warning("no messageid or time with ping", $this->getName());
            $newMessages = array();
        }

        return $newMessages;
    }

}
