<?php

namespace zc\commands;

use \esprit\core\BaseCommand as BaseCommand;
use \esprit\core\Request as Request;
use \esprit\core\Response as Response;
use \esprit\core\exceptions\PageNotFoundException;

use \zc\lib\RoomSource;
use \zc\lib\Room;

/**
 * The command for requests to chat rooms. 
 *
 * @author jbowens
 */
class Command_ROOM extends BaseCommand {

    const COMMAND_NAME = "Room";

    public function getName() {
        return self::COMMAND_NAME;
    }

    public function run(Request $request, Response $response) {

        $url = $request->getUrl();

        if( $url->getPathLength() < 2 || ! $url->getPathPiece(1) )
            throw new PageNotFoundException();

        $roomIdentifier = $url->getPathPiece(1);

        $roomSource = new RoomSource( $this->getDatabaseManager(), $this->getCache() );

        // Get the room from the cache or database
        $room = $roomSource->getRoomByIdentifier( $roomIdentifier );
        $response->set("room", $room);

        $session = $request->getSession();

        return $response;
    } 

}

