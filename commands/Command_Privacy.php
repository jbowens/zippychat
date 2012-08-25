<?php

namespace zc\commands;

use \esprit\core\Request as Request;
use \esprit\core\Response as Response;

use \zc\lib\BaseCommand;

class Command_Privacy extends BaseCommand {

    const COMMAND_NAME = "Privacy";

    public function getName() {
        return self::COMMAND_NAME;
    }

    public function generateResponse(Request $request, Response $response) {
        return $response;
    } 

}

