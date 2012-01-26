<?php

/**
* function to return a valid path - i.e. not malicious or breaking out of the root media dir
*/
function getValidPath($path){
	//insert checks here
	
	return $path;
}
/**
* function to log messages to a file with a verbosity level
*/
function appLog($message, $level = -1){
	global $config;
	$file = fopen($config["logFile"], "a");
	fwrite($file, time() . ": ". $level. " :". $message."\n");
	fclose($file);
}

/**
* replaces placeholders in command strings with sanitized replacements
*/
function expandCmdString($cmd, $path){
	//sanitize replacements
	$path = escapeshellarg($path);

	$patterns = array();
	$patterns[0] = "/%path/";
	
	$replacements = array();
	$replacements[0] = $path;
	
	return preg_replace($patterns, $replacements, $cmd);
}

function var_dump_pre($var){
	echo "<pre>";
	var_dump($var);
	echo "</pre>";
}
/**
* custom error handler 
*/
function appErrorHandler($errNo, $errStr, $errFile, $errLine){
	if (!(error_reporting() & $errNo)) {
        // This error code is not included in error_reporting
        return;
    }
	switch ($errNo) {
		case E_USER_ERROR:
			appLog("PHP ERROR in ${errFile} line ${errLine}:".$errStr,appLog_INFO);
			exit(1);
			break;

		case E_USER_WARNING:
			appLog("PHP Warning in ${errFile} line ${errLine}:".$errStr,appLog_VERBOSE);
			break;

		case E_USER_NOTICE:
			appLog("PHP Notice in ${errFile} line ${errLine}:".$errStr, appLog_VERBOSE);
			break;

		default:
			appLog("Unknown error type in ${errFile} line ${errLine}:".$errStr,appLog_INFO);
			break;
    }
}

function handleExeption($exception){
	appLog("Uncaught PHP Exception: ". var_export($exception,true));
}

?>