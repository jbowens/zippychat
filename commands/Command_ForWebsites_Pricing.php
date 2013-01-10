<?php

namespace zc\commands;

use \esprit\core\Request as Request;
use \esprit\core\Response as Response;

use \zc\lib\BaseCommand;

/**
 * @author jbowens
 */
class Command_ForWebsites_Pricing extends BaseCommand {

    const COMMAND_NAME = "ForWebsites_Pricing";

    public function getName() {
        return self::COMMAND_NAME;
    }

    public function generateResponse(Request $request, Response $response) {

        return $response;
    } 

}

