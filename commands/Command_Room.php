<?php

namespace zc\commands;

use \esprit\core\Request as Request;
use \esprit\core\Response as Response;
use \esprit\core\exceptions\PageNotFoundException;

use \zc\lib\BaseCommand;
use \zc\lib\RoomSource;
use \zc\lib\Room;

/**
 * The command for requests to chat rooms. 
 *
 * @author jbowens
 */
class Command_Room extends BaseCommand {

    const COMMAND_NAME = "Room";
    const CHAT_SESSION_VARIABLE_PREFIX = "chatsession_";

    public function getName() {
        return self::COMMAND_NAME;
    }

    public function run(Request $request, Response $response) {

        // Determine which chat room is being requested
        $room = $this->getRoomFromRequest($request);
        if( $room == null )
            throw new PageNotFoundException();
        $response->set("room", $room);

        $chatSession = $this->getChatSession( $request, $room );
        $response->set('chatSession', $chatSession);

        return $response;
    }

    /**
     * Extract the room from the request.
     *
     * @return the Room object indicated through the url
     */
    public function getRoomFromRequest(Request $request)
    {
        $url = $request->getUrl();
        if( $url->getPathLength() < 2 || ! $url->getPathPiece(1) )
            return null;
        $roomIdentifier = $url->getPathPiece( 1 );

        // Get the room from the cache or database
        $roomSource = $this->getRoomSource();
        $room = $roomSource->getRoomByIdentifier( $roomIdentifier );
        return $room;
    }

    /**
     * Gets the ChatSession object for this user. This will create a new chat session
     * if necessary.
     *
     * @param $request the Request being serviced
     * @param $room the Room the user is connecting to
     * @return the user's ChatSession
     */
    public function getChatSession(Request $request, Room $room) {
        $session = $request->getSession();
        $chatSessionSource = $this->getChatSessionSource();

        // Are they already in this chat room?
        if( $session->keyExists( self::CHAT_SESSION_VARIABLE_PREFIX . $room->getRoomId() ) )
        {
            $chatSessionId = $session->get( self::CHAT_SESSION_VARIABLE_PREFIX . $room->getRoomId() );
            $chatSession = $chatSessionSource->getChatSessionById( $chatSessionId );
        }
        else
        {
            // Create a new chat session
            $chatSession = $chatSessionSource->createSession( $room );
        }
        
        return $chatSession;
    } 

}

