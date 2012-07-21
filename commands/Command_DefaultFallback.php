<?php

namespace zc\commands;

use \esprit\core\BaseCommand as BaseCommand;
use \esprit\core\Request as Request;
use \esprit\core\Response as Response;

/**
 * A default fallback command that should be used when no command matches a given
 * request.
 *
 * @author jbowens 
 */
class Command_DefaultFallback extends BaseCommand {

    const COMMAND_NAME = "DefaultFallback";
    const LOG_SOURCE = "Cmd_DefaultFallback";

    /**
     * See BaseCommand.run(Request $request, Response $response) 
     */
    public function run(Request $request, Response $response) {
        
        $this->getLogger()->info("404 on request to " . $request->getUrl()->getPath(), self::LOG_SOURCE);

        $response->set('IS_404', true);

        return $response;

    }

    public function getName() {
        return self::COMMAND_NAME;
    }

} 
