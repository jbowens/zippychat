<?php

namespace zc\commands;

use \esprit\core\Request;
use \esprit\core\Response;
use \esprit\core\exceptions\BadUserInputException;

use \zc\lib\BaseCommand;
use \zc\lib\RoomAware;

class Command_EmailRoomInvite extends BaseCommand {
    use RoomAware;

    const COMMAND_NAME = "EmailRoomInvite";

    public function getName() {
        return self::COMMAND_NAME;
    }

    public function generateResponse(Request $request, Response $response) {

        try {

            $room = $this->getRequestedRoom( $request );
            if( $room == null )
            {
                $this->error("Recevied change username request with a room");
                $response->set('error', true);
                return $response;
            }

            $toAddress = $request->getPost('to');
            $message = $request->getPost('message');

            if( ! $toAddress )
            {
                // TODO: Verify email
                throw new BadUserInputException( 'to', 'Invalid email address for the recipient.' );
            }

            // It's okay if the user doesn't provide a message, we can still provide a link to the
            // chat room and a generic message.


        } catch( BadUserInputException $ex )
        {
            $response->set('error', true);
            $response->set('field', $ex->getField());
        }
        
        return $response;
    }

}
