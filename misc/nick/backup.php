<?php

$descriptors = array(
	0 => array("pipe", "r"),
	1 => array("pipe", "w"),
	2 => array("pipe", "w"),
);

$cwd = "/var/www/projects/newsubsonic/";

$cmd = "/usr/bin/ffmpeg -i TheJuggler.wmv -f flv -v 0 -vcodec libx264 -";

$process = proc_open($cmd, $descriptors, $pipes, $cwd);

stream_set_timeout($pipes[1], 1);
stream_set_timeout($pipes[2], 1);

echo "<pre>";
while(true){
	$status = proc_get_status($process);
	if(!$status["running"]) break;
	
	sleep(1);
	//var_dump(fread($pipes[1],32));
	if(!feof($pipes[2]));
		var_dump(fread($pipes[2],16));
	flush();
	ob_flush();
}
echo "</pre>";


fclose($pipes[1]);
fclose($pipes[2]);

$retVal = proc_terminate($process);

echo "return val: " . $retVal;



?>