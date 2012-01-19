<?php

ini_set("output_buffering", "0");
//unlink("atest.mp4");

header("Content-Transfer-Encoding: binary");
header("Content-Type: application/octet-stream");
header("Cache-Control: no-cache");

$descriptors = array(
	0 => array("pipe", "r"),
	1 => array("pipe", "w"),
	2 => array("pipe", "w"),
);

$cwd = "/var/www/projects/newsubsonic/";

$cmd = "/usr/bin/ffmpeg -i TheJuggler.wmv -f flv -v 0 -vcodec libx264 -";
//$cmd = "/usr/bin/ffmpeg -i TheJuggler.wmv atest.mp4";

$process = proc_open($cmd, $descriptors, $pipes, $cwd);

// stream_set_timeout($pipes[1], 1);
// stream_set_timeout($pipes[2], 1);

//echo "<pre>";
$starttime = time();
while(true){
	$status = proc_get_status($process);
	if(!$status["running"]) {
		//echo "Finished";
		break;		
	}
	
	//sleep(1);
	var_dump(fread($pipes[1],4096));
	//var_dump(fread($pipes[2],2048));
	flush();
	
	//if(time()-$starttime > 10) break;
}
//echo "</pre>";


fclose($pipes[1]);
fclose($pipes[2]);

posix_setpgid($status["pid"],$status["pid"]);


posix_kill($status["pid"],9);
proc_terminate($process);



?>