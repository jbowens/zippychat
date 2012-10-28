<?php

error_reporting(E_ALL | E_STRICT);

// Configure for more random session ids
ini_set("session.entropy_file", "/dev/urandom");
ini_set("session.entropy_length", "512");

$espritTop = getenv('ESPRIT_TOP') ? getenv('ESPRIT_TOP') : '/var/lib/php/esprit/';
$espritAutoloader = $espritTop . "autoloader.php";

require_once $espritAutoloader;
require_once "autoloader.php";

$config = esprit\core\Config::createFromJSON("data/hosts/" . gethostname() . ".json");
$controller = esprit\core\Controller::createController( $config );

// Setup logging
$logRecorder = new esprit\core\util\FileLogRecorder($config->get('error_log'), esprit\core\util\Logger::WARNING);
$controller->getLogger()->addLogRecorder( $logRecorder );

if( $config->get("debug") ) {
    $fineRecorder = new esprit\core\util\FileLogRecorder($config->get('debug_log'), esprit\core\util\Logger::FINEST);
    $controller->getLogger()->addLogRecorder( $fineRecorder );
}

// Setup our custom catchall
$viewResolverFactory = $controller->createViewResolverFactory();
$catchall = $viewResolverFactory->createCatchallViewResolver( new \zc\views\DefaultView( $config,
                                                                                         $controller->getLogger(),
                                                                                         $controller->getTemplateParser() ) );
$controller->appendViewResolver( $catchall );

// Respond to the user's request
$controller->run();

// Clean up
$controller->close();

