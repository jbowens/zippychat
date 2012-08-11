<?php

namespace zc\commands;

use \esprit\core\Request as Request;
use \esprit\core\Response as Response;

use zc\lib\BaseCommand

class Command_TermsOfUse extends BaseCommand {

    const COMMAND_NAME = "TermsOfUse";

    public function getName() {
        return self::COMMAND_NAME;
    }

    public function run(Request $request, Response $response) {
        return $response;
    } 

}

