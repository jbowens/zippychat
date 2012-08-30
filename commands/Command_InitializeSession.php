<?php

namespace zc\commands;

use \esprit\core\Request;
use \esprit\core\Response;

use \zc\lib\BaseCommand;
use \zc\lib\ChatSession;
use \zc\lib\ChatSessionSource;
use \zc\lib\RoomAware;

/**
 * Initializes the users chat session.
 *
 * @author jbowens
 * @since 2012-08-27
 */
class Command_InitializeSession extends BaseCommand {
    use RoomAware;

    const COMMAND_NAME = "InitializeSession";

    public function getName() {
        return self::COMMAND_NAME;
    }

    public function generateResponse(Request $request, Response $response)
    {
        $room = $this->getRequestedRoom($request);

        // Identify the user's chat session
        $chatSessionSource = $this->getChatSessionSource();
        $chatSession = $chatSessionSource->extractChatSession($request, $room);
        if( $chatSession == null )
        {
            // Not sure how this is possible, but no harm in creating a session for them
            $this->logger->info("The user does not have an existing chat session", $this->getName());
            $chatSession = $chatSessionSource->createSession( $room );
            $request->getSession()->set( ChatSessionSource::CHAT_SESSION_VARIABLE_PREFIX . $room->getRoomId(), $chatSession->getChatSessionId() );
            $response->set('createdNewSession', true);
        }
        else if( ! $chatSession->getActive() )
        {
            // We marked them as inactive. Let's reactivate now that we have a recent request from them
            $this->logger->info("Reactivating user chat session", $this->getName());
            $chatSessionSource->reactivateChatSession( $chatSession );
        }
        $response->set('chatSession', $chatSession);

        // Greatest change id
        $changeId = $chatSessionSource->getMostRecentUernameChangeId();
        $response->set('usernameChangeId', $changeId);

        return $response;
    } 

}
