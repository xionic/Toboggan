<?php
/**
* function to log messages to a file with a verbosity level
*/
function appLog($message, $level = -1){
	global $config;
	if($level > $config["logLevel"]) return; //verbosity cut-off level
	
	$debugInfo = debug_backtrace(1);
	if(count($debugInfo) > 1)
		$callingfn = $debugInfo[1]["function"];
	else
		$callingfn = "[origin unknown]";
	$file = fopen($config["logFile"], "a");
	
	//expand arrays
	if(is_array($message))
		$message = var_export($message,true);
	
	fwrite($file, date("Y/m/d H:i:s") . ": ". $level. ": " . $callingfn . "\t: " . $message."\n");
	fclose($file);
}

/**
* replaces placeholders in command strings with sanitized replacements
*/
function expandCmdString($cmd, $data){

	$allowedPatterns = array(
		"path",
		"bitrate"
	);
	
	$patterns = array();
	$replacements = array();
	
	foreach($allowedPatterns as $item)
	{
		if(isset($data[$item]))
		{
			$patterns[]		 = "/%".$item."/";
			setlocale(LC_CTYPE, "en_GB.UTF-8"); //stop escapeshellarg from stripping non ascii characters
			$replacements[]	 =	escapeshellarg($data[$item]);
		}
	}
	
	return preg_replace($patterns, $replacements, $cmd);
}
/**
* debugging function
*/
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
/**
* Generic exception handler
*/
function handleExeption($exception){
	appLog("Uncaught PHP Exception: ". var_export($exception,true));
}

/**
* function to clean-up and sanitize relative file system paths
*/
function normalisePath($fn){
	$fnArray = explode("/",$fn);
	$ofnArray = array();

	foreach($fnArray as $val)
	{
		if($val == "..")
		{
			//delete last location from array
			array_pop($ofnArray);
		}
		else if ($val != "." && $val != "")
			$ofnArray[] = $val;
	}

	//construct new path
	$newPath = "";
	foreach($ofnArray as $dir)
	{
		$newPath .= "/".$dir;
	}

	return $newPath;
}
/**
* reports errors in an appropriate manner
*/
function reportError($errMsg, $httpcode = 400, $mime = 'text/plain'){
	appLog("Reporting error to user: '".$errMsg."'", appLog_DEBUG);
	if($mime != "text/json") // injection protection
		$errMsg = htmlentities($errMsg);
	restTools::sendResponse	($errMsg,$httpcode, $mime);
	exit;
}
/**
* callback function for argument validation - used by ArgValidator class
*/
function handleArgValidationError($msg, $argName="", $argValue="")
{
	reportError($msg, 400, "text/plain");
	exit;
}

/**
* Report that the server has had an error and inform the user to check the error log on the server. 
*/
function reportServerError($errMsg, $httpcode = 500, $mime = 'text/plain')
{
	appLog("Server Error: '".$errMsg."'", appLog_INFO);
	if($mime != "text/json") // injection protection
		$errMsg = htmlentities($errMsg);
	restTools::sendResponse	("There was an error in the ". APPNAME ." server application. Please check the server log.",$httpcode, $mime);
	exit;
}




?>