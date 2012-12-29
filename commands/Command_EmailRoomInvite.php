<?php

namespace zc\commands;

use \esprit\core\email\Email;
use \esprit\core\email\EmailAddress;
use \esprit\core\Request;
use \esprit\core\Response;
use \esprit\core\exceptions\BadUserInputException;
use \esprit\core\email\TemplatedEmailer;


use \zc\lib\BaseCommand;
use \zc\lib\RoomAware;

class Command_EmailRoomInvite extends BaseCommand {
    use RoomAware;

    const COMMAND_NAME = "EmailRoomInvite";
    const EMAIL_TEMPLATE = 'email_invite';

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

            $email = new Email();
            $email->setFrom(EmailAddress::createFromAddress( 'no-reply@zippychat.com' ));  // TODO: Genericize the domain name
            $email->setSubject('Chat room invitation');                                    // TODO: Use translation string
            $email->addRecipient(EmailAddress::createFromAddress( $toAddress ));           // TODO: Parse this correctly

            // Create an associative array of template parameters
            $templateParams = array(
                "room_url" => "http://zippychat.com/room/" . $room->getUrlIdentifier(),    // TODO: Genericize the domain name
                "room" => $room,
                "user_message" => $message
            );

            // Send the email using the template
            $templatedEmailer = new TemplatedEmailer( $this->getViewManager()->getTemplateParser() );
            $templatedEmailer->sendEmail( $email, self::EMAIL_TEMPLATE, $templateParams ); 

        } catch( BadUserInputException $ex )
        {
            $response->set('error', true);
            $response->set('field', $ex->getField());
        }
        
        return $response;
    }

}
