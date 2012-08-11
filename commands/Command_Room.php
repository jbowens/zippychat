<?php

namespace zc\commands;

use \esprit\core\Request as Request;
use \esprit\core\Response as Response;
use \esprit\core\exceptions\PageNotFoundException;

use \zc\lib\BaseCommand
use \zc\lib\RoomSource;
use \zc\lib\Room;

/**
 * The command for requests to chat rooms. 
 *
 * @author jbowens
 */
class Command_Room extends BaseCommand {

    const COMMAND_NAME = "Room";

    public function getName() {
        return self::COMMAND_NAME;
    }

    public function run(Request $request, Response $response) {

        $url = $request->getUrl();

        if( $url->getPathLength() < 2 || ! $url->getPathPiece(1) )
            throw new PageNotFoundException();

        $roomIdentifier = $url->getPathPiece( 1 );

        // Get the room from the cache or database
        $roomSource = new RoomSource( $this->getDatabaseManager(), $this->getCache() );
        $room = $roomSource->getRoomByIdentifier( $roomIdentifier );
        if( $room == null )
            throw new PageNotFoundException();
        $response->set("room", $room);

        $session = $request->getSession();

        // Are they already in this chat room?
        if( $session->keyExists( 'user_for_room_' . $room->getRoomId() ) )
        {


        }
        else
        {
            // Create a new chat session
        }

        return $response;
    } 

}

