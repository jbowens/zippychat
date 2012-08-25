<?php

error_reporting(E_ALL | E_STRICT);

require_once "../esprit/autoloader.php";
require_once "autoloader.php";

$config = esprit\core\Config::createFromJSON("data/config.json");
$controller = esprit\core\Controller::createController( $config );

// Setup logging
$logRecorder = new esprit\core\util\FileLogRecorder("/opt/local/apache2/htdocs/logs/errors", esprit\core\util\Logger::WARNING);
$controller->getLogger()->addLogRecorder( $logRecorder );

if( $config->get("debug") ) {
    $fineRecorder = new esprit\core\util\FileLogRecorder("/opt/local/apache2/htdocs/logs/debug", esprit\core\util\Logger::FINEST);
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

