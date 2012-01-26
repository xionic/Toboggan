<?php
require_once("functions.php");

function outputStream($file, $streamerID){

	//set errorhandler so errors are captures and not output
	set_error_handler("appErrorHandler");

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
		0 => array("pipe", "r"), //STDIN
		1 => array("pipe", "w"), //STDOUT
		2 => array("pipe", "w"), //STDERR
	);
	
	//current working dir
	$cwd = $config["basedir"];

	//expand placeholders in command string
	$cmd = expandCmdString($streamerObj["cmd"], $file);

	//start the process
	$process = proc_open($cmd, $descriptors, $pipes, $cwd);
	appLog("Running transcode: ". $cmd, appLog_DEBUG);
	
	//stop output streams from blocking
	stream_set_blocking($pipes[1],0); //STDOUT
	stream_set_blocking($pipes[2],0); //STDERR
	
	$previousPointerLoc=0; // var to remember how many bytes were last read from STDOUT once the process is dead
	$output = null;
	
	while(true){
		
		//try to prevent php timeouts
		set_time_limit(60);
	
		//get a chunk of data
		$output = fread($pipes[1],8192);
		print $output;
		
		//get error output
		$errOutput = fgets($pipes[2]);
		if($errOutput != "")
			appLog("STREAMER_ERRSTREAM: ".$errOutput,appLog_VERBOSE);
		
		//get process status
		$status = proc_get_status($process);
		
		//continue until process stops
		if(!$status["running"]) {
			//continue until all remaining data in the stream buffer is read
			appLog("Streamer STDOUT Pointer moved: ". (ftell($pipes[1])-$previousPointerLoc) . " bytes", appLog_DEBUG);
			if((ftell($pipes[1])-$previousPointerLoc) == 0){
				break;
			}
			$previousPointerLoc = ftell($pipes[1]);
		}
		flush();
		
		//give the streamer a chance
		usleep(25000);
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