<?php
require_once("functions.php");

function outputStream($file, $streamerID){

	//gloabl config
	global $config;

	//streamer settings
	$streamerObj = getStreamerById($streamerID);
	
	//do not buffer
	ini_set("output_buffering", "0");


	header("Content-Type: " . $streamerObj["mime"]);
	header("Content-disposition: attachment; filename=media.stream");

	header("Cache-Control: no-cache");

	//stream descriptors
	$descriptors = array(
		0 => array("pipe", "r"),
		1 => array("pipe", "w"),
		2 => array("pipe", "w"),
	);
	
	//current working dir
	$cwd = $config["basedir"];

	//expand placeholders in command string
	$cmd = expandCmdString($streamerObj["cmd"], $file);

	//start the process
	$process = proc_open($cmd, $descriptors, $pipes, $cwd);
	appLog("Running transcode: ". $cmd, appLog_DEBUG);
	
	while(!feof($pipes[1])){
		//get process status
		$status = proc_get_status($process);
		//continue until process stops -- NEEDS FEOF CHECK!!!!!!!!!
		if(!$status["running"]) {
			break;		
		}
		//output chunk of data
		print fread($pipes[1],20480);
		flush();
	}
	
	appLog("Finished transcode: ". $cmd, appLog_DEBUG);
	
	//fclose($pipes[1]);
	fclose($pipes[2]);

	//set the process group pid
	posix_setpgid($status["pid"],$status["pid"]);

	//kill the process to ensure that the ffmpeg sub process is dead
	posix_kill($status["pid"],9);
	
	//terminate the process
	proc_terminate($process);


}

?>