<?php

error_reporting(E_ALL | E_STRICT);

// Configure for more random session ids
ini_set("session.entropy_file", "/dev/urandom");
ini_set("session.entropy_length", "512");

$espritTop = getenv('ESPRIT_TOP') ? getenv('ESPRIT_TOP') : '/var/lib/php/esprit/';
$espritAutoloader = $espritTop . "autoloader.php";

require_once $espritAutoloader;
require_once "autoloader.php";

$config = esprit\core\Config::createFromJSON("data/hosts/" . gethostname() . "-" . get_current_user() . ".json");
$controller = esprit\core\Controller::createController( $config );

// Setup logging
if( $config->settingExists('error_log') ) {
    // Note: error logging is likely already setup through the esprit default_error_logfile option.
    $logRecorder = new esprit\core\util\FileLogRecorder($config->get('error_log'), esprit\core\util\Logger::WARNING);
    $controller->getLogger()->addLogRecorder( $logRecorder );
}

if( $config->get("debug") && $config->settingExists('debug_log') ) {
    $fineRecorder = new esprit\core\util\FileLogRecorder($config->get('debug_log'), esprit\core\util\Logger::FINEST);
    $controller->getLogger()->addLogRecorder( $fineRecorder );
}

// Setup our custom catchall
$viewResolverFactory = $controller->createViewResolverFactory();
$catchall = $viewResolverFactory->createCatchallViewResolver( new \zc\views\DefaultView( $config,
                                                                                         $controller->getLogger(),
                                                                                         $controller->getTemplateParser() ) );
$controller->appendViewResolver( $catchall );

// Setup our request flaggers
$mobileFlagger = new \esprit\flaggers\MobileFlagger();
$controller->registerRequestFlagger( $mobileFlagger );
if( $config->get('flash_flagger') )
    $controller->registerRequestFlagger( new \zc\lib\FlashFlagger() );

// Respond to the user's request
$controller->run();

// Clean up
$controller->close();

