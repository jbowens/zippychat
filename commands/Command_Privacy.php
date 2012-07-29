<?php

namespace zc\commands;

use \esprit\core\BaseCommand as BaseCommand;
use \esprit\core\Request as Request;
use \esprit\core\Response as Response;

class Command_Privacy extends BaseCommand {

    const COMMAND_NAME = "Privacy";

    public function getName() {
        return self::COMMAND_NAME;
    }

    public function run(Request $request, Response $response) {
        return $response;
    } 

}

