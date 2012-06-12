<?php
require_once("functions.php");

function outputStream($streamerID, $file){

	//set errorhandler so errors are captures and not output
	set_error_handler("appErrorHandler");
	
	//check file exist
	if(!is_file($file) && !is_link($file))
	{
		appLog("File does not exist: ".$file, appLog_INFO);
		reportError("Requested file does not exist");
		return false;
	}

	appLog("Streaming file: ". $file, appLog_VERBOSE);
	
	//info about the file and path
	$filepathInfo = pathinfo($file);
	
	//global config
	global $config;
	
	//default mime
	$mimeType = "application/octet-stream";
	
	//check if this is a download
	if($streamerID == 0){
		appLog("This is a download - going straight to passthrough", appLog_DEBUG);
		$filenameToSend = $filepathInfo["basename"]; // filename for the http header
	}
	else{	
		//streamer settings
		$streamerObj = getStreamerById($streamerID);
		if(!$streamerObj)
		{
			appLog("Streamer object with id $streamerID does not exist", appLog_INFO);
			reportError("Invalid StreamerID");
			return false;
		}
		//check that the extension is compatible with the streamer
		if(strtolower($streamerObj->fromExt) != strtolower($filepathInfo["extension"]))
		{
			appLog("Streamer does not support this extension", appLog_INFO);
			reportError("Streamer specified by streamerID does not support this file");
			return false;
		}
	
		appLog("Using Streamer ID: ". $streamerObj->id, appLog_VERBOSE);			
		
		/**
		* Get media bitrate
		*/
		$maxBitrate = getCurrentMaxBitrate($streamerObj->outputMediaType); //get from db in the end
		if($maxBitrate == 0 || $maxBitrate === false)
		{
			appLog("Could not retreive maxBitrate or it was 0 - ignoring", appLog_INFO);
			$maxBitrate = NO_MAX_BITRATE; // failover to no max bitrate
		}
		appLog("Max bitrate to be applied is: ". $maxBitrate, appLog_DEBUG);
		$mustAdjustBitrate = false;
		
		if($maxBitrate != NO_MAX_BITRATE)//if there is a max bitrate
		{ 
			//get the bitrate command with variables expanded
			$bitrateCommand = expandCmdString($streamerObj->bitrateCmd, 
				array(
					"path" 		=> $file,
				)
			);
			//check that the bitrate command is defined
			if(!$bitrateCommand){ 
				appLog("No valid command to get bitrate - skipping", appLog_VERBOSE);
				$mustAdjustBitrate = true; //don't know source file bitrate - be safe and transcode
			}
			else{ // if there is a command to get the bitrate - get the bitrate and compare with the max
				appLog("Retreiving bitrate from file using command: ". $bitrateCommand, appLog_DEBUG);
				$bitrateOutput = exec($bitrateCommand);
				appLog("Output from bitrate command: ". $bitrateOutput, appLog_DEBUG);
				$bitrate = (int) $bitrateOutput;
				appLog("Parsed bitrate number read from bitrate command output is " . ($bitrate==0? "invalid - no bitrate limit will be applied":$bitrate."kb/s"), appLog_DEBUG);
				if($bitrate == 0)
					appLog("Parsed bitrate number was 0 - no bitrate limit will be applied", appLog_DEBUG);				
			
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
		//mimetype of file to be output
		$mimeType = $streamerObj->mime;		
		
		//filename to send
		$filenameToSend = substr($filepathInfo["basename"], 0, strrpos($filepathInfo["basename"], ".")). "." . $streamerObj->toExt;
		
	}// end if download
	
	/**
	* setup for streaming data and headers
	*/
	//sanitise filename
	$filenameToSend = preg_replace("/\"/","'",$filenameToSend);
	
	//do not buffer
	ini_set("output_buffering", "0");

	appLog("Using mime type: ". $mimeType, appLog_DEBUG);
	header("Content-Type: " . $mimeType);
	
	appLog("Using attachement filename: $filenameToSend", appLog_DEBUG);
	header("Content-disposition: attachment; filename=".$filenameToSend);

	header("Cache-Control: no-cache");
	
	/**
	* Output the stream
	*/
	//make sure the session is closed because otherwise other requests will be blocked by php
	session_write_close();
	
	if(
		$streamerID == 0 || //straight passthrough file download
		(!$mustAdjustBitrate && $streamerObj->toExt == $streamerObj->fromExt)
	) // if the file's bitrate does not need to be change and the format is supported by the player, do not transcode
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

	//limit bandwidth
	$maxBandwidth = getCurrentMaxBandwidth();
	if(!(is_numeric($maxBandwidth) && $maxBandwidth >= 0))
	{
		reportError("Could not get maxBandwidth or it is 0");
		exit();
	}
	appLog("Limiting bandwidth to ". $maxBandwidth . "KB/s", appLog_DEBUG);
	
	//calc bytes to read each second
	$bytesToRead = $maxBandwidth*1024;
	
	while(!feof($fh))
	{
		//traffic limits
		$remainingTraffic = getRemainingUserTrafficLimit(userLogin::getCurrentUserID());
		if($remainingTraffic !== false)
		{
			if($remainingTraffic <= 0)
			{
				appLog("Traffic limit exceeded for user with id: " . userLogin::getCurrentUserID(), appLog_INFO);
				exit();
			}
			elseif($remainingTraffic < $bytesToRead/1024)
			{
				appLog("Setting \$bytesToRead to $bytesToRead", appLog_DEBUG);
				$bytesToRead = $remainingTraffic*1024;
			}
		}
		//get start position of file pointer
		$pointerStart = ftell($fh);
		
		//start time for bandwidth throttling
		$startTime = microtime(true);
		
		//read the file and output the data
		print(fread($fh, $bytesToRead));
		
		//calc the number of bytes actually read
		$bytesRead = ftell($fh) - $pointerStart;
		
		//update traffic used for traffic limit
		updateUserUsedTraffic(userLogin::getCurrentUserID(), (int)($bytesRead/1024));
		
		//sleep for 1 second minus the time taken to read the data - for bandwidth limiting
		$sleeptime = (1 - (microtime(true) - $startTime))*1000000;
		usleep($sleeptime);
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
	$cmd = expandCmdString($streamerObj->cmd, 
		array(
			"path" 		=> $file,
			"bitrate"	=> getCurrentMaxBitrate($streamerObj->outputMediaType),
		)
	);

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
	
	//bandwidth limit
	$maxBandwidth = getCurrentMaxBandwidth();
	if(!(is_numeric($maxBandwidth) && $maxBandwidth >= 0))
	{
		reportError("Could not get maxBandwidth or it is 0");
		exit();
	}
	appLog("Limiting bandwidth to ". $maxBandwidth . "KB/s", appLog_DEBUG);
	
	//number times per sec that we need to fread to output the max bandwidth
	$iterationsPerSec = getCurrentMaxBandwidth()/8;
	/**
	* loop until trancode process dies outputing the data stream
	*/
	$bytesToRead = 8192; // this is the normal size to read, it will be reduced in order to hit the traffic limit and not exceed it.
	while(true){
	
		//start time for bandwidth throttling
		$startTime = microtime(true);
		
		//try to prevent php timeouts
		set_time_limit(60);
		
		//check traffic limit
		$remainingTraffic = getRemainingUserTrafficLimit(userLogin::getCurrentUserID());
		if($remainingTraffic !== false)
		{
			if($remainingTraffic <= 0)
			{
				appLog("Traffic limit exceeded for user with id: " . userLogin::getCurrentUserID(), appLog_INFO);
				exit();
			} // adjust the number of bytes to read next time to avoid overstepping the limit
			elseif($remainingTraffic < $bytesToRead/1024)
			{
				appLog("Setting \$bytesToRead to $bytesToRead", appLog_DEBUG);
				$bytesToRead = $remainingTraffic*1024;
			}
		}
		
		//get a chunk of data
		$output = fread($pipes[1],$bytesToRead);
		print $output;
		
		//update traffic used for traffic limit
		updateUserUsedTraffic(userLogin::getCurrentUserID(), (int)($bytesToRead/1024));
		
		//get error output
		$errOutput = fgets($pipes[2]);
		if($errOutput != "")
			appLog("STREAMER_ERRSTREAM: ".$errOutput,appLog_DEBUG2);
		
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