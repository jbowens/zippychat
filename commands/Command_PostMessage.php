<?php

namespace zc\commands;

use \esprit\core\Request;
use \esprit\core\Response;
use \esprit\core\exceptions\PageNotFoundException;

use \zc\lib\BaseCommand;
use \zc\lib\ChatSession;
use \zc\lib\ChatSessionSource;
use \zc\lib\Message;
use \zc\lib\MessageSource;
use \zc\lib\RoomAware;

/**
 * Command responsible for posting messages to chat rooms.
 *
 * @author jbowens
 */
class Command_PostMessage extends BaseCommand
{
    use RoomAware;
    
    const COMMAND_NAME = "PostMessage";
    const ERROR_NOT_LOGGED_IN = 1;
    const ERROR_NO_MESSAGE = 2;

    public function getName() {
        return self::COMMAND_NAME;
    }

    public function generateResponse(Request $request, Response $response) {

        // Identify the requested room
        $room = $this->getRequestedRoom($request);
        if( $room == null )
        {
            $this->info("No valid room id accompany post-message request.");
            throw new PageNotFoundException(); 
        }

        // Identify the user's chat session
        $chatSessionSource = $this->getChatSessionSource();
        $chatSession = $chatSessionSource->extractChatSession($request, $room);
        if( $chatSession == null )
        {
            $response->set('errorCode', self::ERROR_NOT_LOGGED_IN);
            $response->set('noSession', true);
            return $response;
        }
        else if( ! $chatSession->getActive() )
        {
            // We marked them as inactive. Let's reactivate now that we have a recent request from them
            $this->logger->info("Reactivating user chat session", $this->getName());
            $chatSessionSource->reactivateChatSession( $chatSession );
        }
        $response->set('chatSession', $chatSession);

        if( ! $request->getPost('msg') )
        {
            $response->set('noMessage', true);
            $response->set('errorCode', self::ERROR_NO_MESSAGE);
            return $response;
        }

        $messageSource = $this->getMessageSource();
        $newMessage = $messageSource->createMessage( $room, $chatSession, $request->getPost('msg') );
        $response->set('newMessage', $newMessage);

        return $response;

    }

}
