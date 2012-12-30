<?php

namespace zc\commands;

use \esprit\core\Request;
use \esprit\core\Response;
use \esprit\core\exceptions\PageNotFoundException;

use \zc\lib\BaseCommand;
use \zc\lib\ChatSession;
use \zc\lib\ChatSessionSource;
use \zc\lib\RoomAware;
use \zc\lib\RoomSource;

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

        // Ensure that this room exists
        if( $room == null )
            throw new PageNotFoundException(); 

        // Identify or create the user's chat session... if they're authenticated
        $chatSessionSource = $this->getChatSessionSource();
        $chatSession = $chatSessionSource->extractChatSession($request, $room);
        if( $chatSession == null )
        {
            $this->logger->info("The user does not have an existing chat session", $this->getName());
            if( $room->isPasswordProtected() )
            {
                if( $request->getPost('password') )
                {
                    // The client provided a password attempt
                    $passwordMatches = RoomSource::getPasswordHasher()->matchesHash( $request->getPost('password'),
                                                                                     $room->getPasswordHash() );
                    if( $passwordMatches )
                    {
                        $chatSession = $chatSessionSource->createSession( $room );
                    }
                    else
                    {
                        $response->set('badPassword', true);
                    }
                }
                else
                {
                    // Alert the client that we need a password in order to
                    // create a chat session for them.
                    $response->set('requirePassword', true);
                }
            }
            else
            {
                $chatSession = $chatSessionSource->createSession( $room );
            }

            // Check if we created a new session
            if( $chatSession != null )
            {
                $request->getSession()->set( ChatSessionSource::CHAT_SESSION_VARIABLE_PREFIX . $room->getRoomId(), $chatSession->getChatSessionId() );
                $response->set('createdNewSession', true);
            }
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
