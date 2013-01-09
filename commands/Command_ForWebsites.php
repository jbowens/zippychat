<?php

namespace zc\commands;

use \esprit\core\Request as Request;
use \esprit\core\Response as Response;

use \zc\lib\BaseCommand;
use \zc\lib\RoomSource;
use \zc\lib\Room;

/**
 * @author jbowens
 */
class Command_ForWebsites extends BaseCommand {

    const COMMAND_NAME = "ForWebsites";

    public function getName() {
        return self::COMMAND_NAME;
    }

    public function generateResponse(Request $request, Response $response) {

        return $response;
    } 

}

