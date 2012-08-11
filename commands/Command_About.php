<?php

namespace zc\commands;

use zc\lib\BaseCommand;

use \esprit\core\Request as Request;
use \esprit\core\Response as Response;

class Command_About extends BaseCommand {

    const COMMAND_NAME = "About";

    public function getName() {
        return self::COMMAND_NAME;
    }

    public function run(Request $request, Response $response) {
        return $response;
    } 

}

