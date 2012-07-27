<?php

error_reporting(E_ALL | E_STRICT);

require_once "../web-lib/esprit/autoloader.php";
require_once "autoloader.php";

$config = esprit\core\Config::createFromJSON("data/config.json");
$controller = esprit\core\Controller::createController( $config );

// Setup logging
$logRecorder = new esprit\core\util\FileLogRecorder("/var/www/logs/errors", esprit\core\util\Logger::WARNING);
$controller->getLogger()->addLogRecorder( $logRecorder );

if( $config->get("debug") ) {
    $fineRecorder = new esprit\core\util\FileLogRecorder("/var/www/logs/debug", esprit\core\util\Logger::FINEST);
    $controller->getLogger()->addLogRecorder( $fineRecorder );
}

// Respond to the user's request
$controller->run();

// Clean up
$controller->close();

