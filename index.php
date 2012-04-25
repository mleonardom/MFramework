<?php
	session_name("MF");
	session_start();
	
	error_reporting(E_ALL);
	ini_set('display_errors','On');
	
    // Define path to application directory
	defined('APPLICATION_PATH')
	    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/application'));
	
	// Ensure library/ is on include_path
	set_include_path(implode(PATH_SEPARATOR, array(
	    realpath(APPLICATION_PATH . '/../library'),
	    get_include_path(),
	)));
	
	// set the base URL
	define('BASE_URL','MFramework');
	
	// set environtment
	define('ENVIROTMENT','development'); // can be: production or development
	
	require_once(APPLICATION_PATH.'/.config/application.php');
	require_once 'MF/Application.php';
	
    $application = new MF_Application();
    $application->getBootstrap()->_run();
?>
