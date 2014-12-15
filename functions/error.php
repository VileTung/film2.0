<?php

/**
* @author Kevin
* @copyright 2014
* @info Catch errors
*/

//Regular error catching
function errorHandling($errno, $errstr, $errfile, $errline)
{
	global $logging;
	
	if (!(error_reporting() & $errno)) {
		// This error code is not included in error_reporting
		return;
	}

	switch ($errno) {
	case E_USER_ERROR:
	case E_ERROR:
		//Message
		$logging->error("PHP Error '" . $errstr . "' (" . $errno . " - " . $errfile . " - " . $errline . ")!");
		exit(1);
		break;

	case E_USER_WARNING:
		//Message
		$logging->error("PHP Warning '" . $errstr . "' (" . $errno . " - " . $errfile . " - " . $errline . ")!");
		break;

	case E_USER_NOTICE:
		//Message
		$logging->error("PHP Notice '" . $errstr . "' (" . $errno . " - " . $errfile . " - " . $errline . ")!");
		break;

	default:
		//Message
		$logging->error("PHP Unknown Error '" . $errstr . "' (" . $errno . " - " . $errfile . " - " . $errline . ")!");
		break;
	}

	//Stop the script
	return true;
}

//Fatal error catching
function fatalError()
{
	$error = error_get_last();
	
	if ($error["type"]==E_ERROR)
	{
		errorHandling($error["type"], $error["message"], $error["file"], $error["line"]);
	}
}

//Register the functions, only in CLI
if (php_sapi_name() == "cli")
{
	register_shutdown_function("fatalError");
	set_error_handler("errorHandling");
}

?>