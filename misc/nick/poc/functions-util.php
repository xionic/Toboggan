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
function appLog($message, $level){
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

?>