<?php

$config = array();

//log levels
define("appLog_DEBUG", 5);
define("appLog_VERBOSE", 4);
define("appLog_INFO",3);

//basedir --will be replaced by config interface
$config["basedir"] = "/mnt/storage/video/TV/";

//file to log messages to
$config["logFile"] = "/tmp/ultrasonic.log";

//array of streamers - from => to => cmd
$config["videoStreamers"] = array(
	array(
		"id"		=> 1,
		"fromExt" 	=> "avi",
		"toExt" 	=> "flv",
		"cmd" 		=> "/usr/bin/ffmpeg -i %path -async 1 -b 700k -s 320x240 -ar 44100 -ac 2 -v 0 -f flv -vcodec libx264 -preset superfast -",
		"mime" 		=> "video/flv",
	),
);

$config["supportedPlayFormatExts"] = array(
	"flv",
);

?>