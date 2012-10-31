<?php

namespace zc\commands;

use \esprit\core\Request;
use \esprit\core\Response;

use \zc\lib\BaseCommand;
use \zc\lib\RoomAware;

/**
 * This command is called by client-side js to change a user's
 * username in a specific chat room.
 *
 * @author jbowens
 * @since 2012-08-28
 */
class Command_ChangeUsername extends BaseCommand {
    use RoomAware;

    const COMMAND_NAME = "ChangeUsername";

    public function getName() {
        return self::COMMAND_NAME;
    }

    public function generateResponse(Request $request, Response $response) {

        $room = $this->getRequestedRoom( $request );
        if( $room == null )
        {
            $this->error("Recevied change username request with a room");
            $response->set('error', true);
            return $response;
        }

        // Identify the user's chat session
        $chatSessionSource = $this->getChatSessionSource();
        $chatSession = $chatSessionSource->extractChatSession($request, $room);

        // What if they don't have an existing session?   
        if( $chatSession == null )
        {
            $this->logger->info("The user does not have an existing chat session", $this->getName());
            $response->set('error', true);
            return $response;
        }

        // They must provide a new username
        if( ! $request->getPost('newUsername') )
        {
            $this->error("Recieved request for a username change without a new username");
            $response->set('error', true);
            return $response;
        }

        $success = false;
        try {
            // Attempt to change the username
            $success = $chatSessionSource->changeUsername( $chatSession, $request->getPost('newUsername') );
        } catch( \InvalidArgumentException $ex )
        {
            // An exception can occur when the provided username is disallowed.
            $this->info("Received an invalid username: " . $request->getPost('newUsername'));
            $response->set("error", true);
            return $response;
        }

        $response->set('changeUsernameSuccess', $success);
        return $response;
    } 

}
