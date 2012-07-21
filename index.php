<?php

error_reporting(E_ALL | E_STRICT);

require_once "../web-lib/esprit/autoloader.php";
require_once "autoloader.php";

$config = esprit\core\Config::createFromJSON("data/config.json");
$controller = new esprit\core\Controller( $config );

// Setup logging
$logRecorder = new esprit\core\util\FileLogRecorder("/var/www/logs/errors", esprit\core\util\Logger::WARNING);
$controller->getLogger()->addLogRecorder( $logRecorder );

if( $config->get("debug") ) {
    $fineRecorder = new esprit\core\util\FileLogRecorder("/var/www/logs/debug", esprit\core\util\Logger::FINEST);
    $controller->getLogger()->addLogRecorder( $fineRecorder );
}

// Setup command source
$commandSource = $controller->createBaseCommandSource("\\zc\\commands", "/var/www/commands");
$commandSources = array( $commandSource );

// Setup the command resolvers
$resolverFactory = $controller->createCommandResolverFactory();

$pathResolver = $resolverFactory->createPathCommandResolver($commandSources);
$controller->appendCommandResolver( $pathResolver );

// Setup the view resolvers
$pathViewResolver = $controller->createPathViewResolver(array('/var/www/views/'), 'php');
$controller->appendViewResolver( $pathViewResolver );

$catchall = $controller->createCatchallViewResolver();
$controller->appendViewResolver( $catchall );

// Respond to the user's request
$controller->run();

// Clean up
$controller->close();

