<?php
require_once("functions.php");

function outputStream($file, $streamerID){

	//set errorhandler so errors are captures and not output
	set_error_handler("appErrorHandler");
	
	//check file exist
	if(!is_file($file) && !is_link($file))
	{
		appLog("File does not exist: ".$file, appLog_INFO);
		return false;
	}

	appLog("Streaming file: ". $file, appLog_VERBOSE);
	
	//global config
	global $config;

	//streamer settings
	$streamerObj = getStreamerById($streamerID);
	appLog("Using Streamer ID: ". $streamerObj->id, appLog_VERBOSE);
	
	//do not buffer
	ini_set("output_buffering", "0");


	header("Content-Type: " . $streamerObj->mime);
	header("Content-disposition: attachment; filename=media.stream");

	header("Cache-Control: no-cache");
	
	/**
	* Get media bitrate
	*/
	//testing
	$maxBitrate = 191; //get from db in the end
	$mustAdjustBitrate = false;
	
	if($maxBitrate != NO_MAX_BITRATE)//if there is a max bitrate
	{ 
		$bitrateCommand = expandCmdString($streamerObj->bitrateCmd, $file);
		if(!$bitrateCommand){ //check that the bitrate command is defined
			appLog("No valid command to get bitrate - skipping", appLog_VERBOSE);
			$mustAdjustBitrate = true; //don't know source file bitrate - be safe and transcode
		}
		else{			
			appLog("Retreiving bitrate from file using command: ". $bitrateCommand, appLog_DEBUG);
			$bitrate = (int) exec($bitrateCommand);
			appLog("Bitrate read from file: " . $bitrate . "kb/s", appLog_DEBUG);		
		
			//check if max bitrate is over 
			if($bitrate > $maxBitrate)
			{
				appLog("Source file bitrate($bitrate) is greater than the max player bitrate($maxBitrate) - need to change bitrate", appLog_DEBUG);
				$mustAdjustBitrate = true;
			}
		}
	}
	else//no max bitrate
	{ 
		appLog("No maximum bitrate is to be applied", appLog_VERBOSE);
		$mustAdjustBitrate = false;
	}
	
	/**
	* Output the stream
	*/
	if(!$mustAdjustBitrate && $streamerObj->toExt == $streamerObj->fromExt) // if the file's bitrate does not need to be change and the format is supported by the player, do not transcode
	{
		appLog("File does not need to be transcoded, streaming straight through", appLog_VERBOSE);
		passthroughStream($file);
	}
	else
	{	
		appLog("File must be transcoded", appLog_VERBOSE);
		transcodeStream($streamerObj, $file);
	}
	
}
/**
* Streams a file straight through as is
*/
function passthroughStream($file){

	$fh = @fopen($file,'rb');
	if(!$fh)
	{
		throw new Exception('Unable to open file');
		exit;
	}

	$fileSize = filesize($file);
	header("Content-Length: " . $fileSize);

	while(!feof($fh))
	{
		print(fread($fh, getCurrentMaxBandwidth()*1024));
		usleep(1000000);
	}

	fclose($fh);
}

/**
* Transcodes a file using the streamerObj data provideded and streams it out
*/
function transcodeStream($streamerObj, $file){
	/**
	* Set up streamer execution environment
	*/
	//stream descriptors
	$descriptors = array(
		0 => array("pipe", "r"), //STDIN
		1 => array("pipe", "w"), //STDOUT
		2 => array("pipe", "w"), //STDERR
	);

	//expand placeholders in command string
	$cmd = expandCmdString($streamerObj->cmd, $file);

	//start the process
	$process = proc_open($cmd, $descriptors, $pipes, sys_get_temp_dir());
	appLog("Running streamer: ". $cmd, appLog_VERBOSE);
	
	if(!is_resource($process)){
		throw new Exception("Streamer process failed to start");
		exit();
	}
	
	//stop output streams from blocking
	stream_set_blocking($pipes[1],0); //STDOUT
	stream_set_blocking($pipes[2],0); //STDERR
	
	$previousPointerLoc=0; // var to remember how many bytes were last read from STDOUT once the process is dead
	$output = null;
	
	//number times per sec that we need to fread to output the max bandwidth
	$iterationsPerSec = getCurrentMaxBandwidth()/8;
	/**
	* loop until trancode process dies outputing the data stream
	*/
	while(true){
		
		//start time for bandwidth throttling
		$startTime = microtime(true);
		
		//try to prevent php timeouts
		set_time_limit(60);
	
		//get a chunk of data
		$output = fread($pipes[1],8192);
		print $output;
		
		//get error output
		$errOutput = fgets($pipes[2]);
		if($errOutput != "")
			appLog("STREAMER_ERRSTREAM: ".$errOutput,appLog_DEBUG);
		
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
		//flush();
		
		//sleep for 1 second minus the time taken to read the data - for bandwidth limiting
		$sleeptime = ((1 - (microtime(true) - $startTime))*1000000)/$iterationsPerSec;
		usleep($sleeptime);
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