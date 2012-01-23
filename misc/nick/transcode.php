<?php

//do not buffer
ini_set("output_buffering", "0");

$filename = "test.avi";

//header("Content-Transfer-Encoding: binary");
//header("Content-Type: audio/mp3");
header("Content-Type: video/flv");
header("Content-disposition: attachment; filename=".$filename.".flv");

header("Cache-Control: no-cache");

nlog("Content type detected: " . mime_content_type($filename));

$descriptors = array(
	0 => array("pipe", "r"),
	1 => array("pipe", "w"),
	2 => array("pipe", "w"),
);

$cwd = "/var/www/projects/ultrasonic/misc/nick/";

//flv transcode
$cmd = "/usr/bin/ffmpeg -i $filename -async 1 -f flv -v 0 -vcodec libx264 -ar 44100 -ac 2 -preset superfast -threads 0 -s 320x240 -b 700k -";

//mp3 transcode
//$cmd = "/usr/bin/ffmpeg -i $filename -v 0 -f mp3 -ab 128k -";

nlog("Starting transcode");

$process = proc_open($cmd, $descriptors, $pipes, $cwd);

//stream_set_timeout($pipes[1], 1);
//stream_set_timeout($pipes[2], 1);


//$starttime = time();
$errStream = "";

while(true){
	//get process status
	$status = proc_get_status($process);
	if(!$status["running"]) {
		break;		
	}
	//nlog("before stream read");
	print fread($pipes[1],20480);
	//nlog("after stream read");
	//$errStream .= fread($pipes[2],2048);
	flush();
	
	//if(time()-$starttime > 10) break;
}

//fpassthru($pipes[1]);

nlog("error stream text: " . $errStream);
nlog("Transcode complete");

//fclose($pipes[1]);
fclose($pipes[2]);

posix_setpgid($status["pid"],$status["pid"]);

nlog("Killing transcode process");
posix_kill($status["pid"],9);
proc_terminate($process);

function nlog($message){
//var_dump($message);
	$file = fopen("/tmp/us_transcodelog.log", "a");
	fwrite($file, $message."\n");
	fclose($file);
}



?>