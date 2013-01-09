<?php

namespace zc\commands;

use \esprit\core\Request;
use \esprit\core\Response;
use \esprit\core\exceptions\PageNotFoundException;

use \zc\lib\BaseCommand;

class Command_FlashCheck extends BaseCommand
{

    const COMMAND_NAME = "FlashCheck";

    public function getName() {
        return self::COMMAND_NAME;
    }

    public function generateResponse(Request $request, Response $response) {
        
        if( ! $request->getPost('flash') )
            throw new PageNotFoundException();

        $request->getSession()->set('flashEnabled', $request->getPost('flash') === 'true');

        return $response;
    }

}
