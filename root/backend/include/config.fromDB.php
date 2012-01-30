<?php

//file to log messages to
$config["logFile"] = "/tmp/ultrasonic.log";

//array of streamers 
$config["videoStreamers"] = array(
	array(
		"id"		=> 1,
		"fromExt" 	=> "avi",
		"toExt" 	=> "flv",
		"cmd" 		=> "/usr/bin/ffmpeg -i %path -async 1 -b 700k -s 320x240 -ar 44100 -ac 2 -v 0 -f flv -vcodec libx264 -preset superfast -",
		"mime" 		=> "video/flv",
		"outputMediaType"	=> 'v',
		"bitrateCmd"	=> "/usr/bin/ffmpeg -i %path 2>&1 | sed -n -e 's/.*bitrate: \\([0-9]\\+\\).*/\\1/p'",
	),
	array(
		"id"		=> 2,
		"fromExt" 	=> "mp4",
		"toExt" 	=> "flv",
		"cmd" 		=> "/usr/bin/ffmpeg -i %path -async 1 -b 700k -s 320x240 -ar 44100 -ac 2 -v 0 -f flv -vcodec libx264 -preset superfast -",
		"mime" 		=> "video/flv",
		"outputMediaType"	=> 'v',
		"bitrateCmd"	=> "",
	),
	array(
		"id"		=> 3,
		"fromExt" 	=> "wmv",
		"toExt" 	=> "flv",
		"cmd" 		=> "/usr/bin/ffmpeg -i %path -async 1 -b 700k -s 320x240 -ar 44100 -ac 2 -v 0 -f flv -vcodec libx264 -preset superfast -",
		"mime" 		=> "video/flv",
		"outputMediaType"	=> 'v',
		"bitrateCmd"	=> "",
	),
	array(
		"id"		=> 4,
		"fromExt" 	=> "mp3",
		"toExt" 	=> "mp3",
		"cmd" 		=> "ffmpeg -i %path -ab 128 -v 0 -f mp3 -",
		"mime" 		=> "audio/mp3",
		"outputMediaType"	=> 'a',
		"bitrateCmd"	=> "/usr/bin/ffmpeg -i %path 2>&1 | sed -n -e 's/.*bitrate: \\([0-9]\\+\\).*/\\1/p'",
	),
);



?>