<?php

error_reporting(E_ALL | E_STRICT);

require_once "../web-lib/esprit/autoloader.php";

$config = esprit\core\Config::createFromJSON("data/config.json");
$controller = new esprit\core\Controller( $config );

// Setup logging
$logRecorder = new esprit\core\util\FileLogRecorder("/var/www/logs/errors", esprit\core\util\Logger::WARNING);
$controller->getLogger()->addLogRecorder( $logRecorder );

// Setup the command resolvers
$pathResolver = $controller->createPathCommandResolver(array('/var/www/commands/'), 'php');
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

