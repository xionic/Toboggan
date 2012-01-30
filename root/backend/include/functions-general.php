<?php
/**
* get a config option
*/
function getConfig($name){
	global $config;
	return $config[$name];
}



/**
* get an object which represents data about a media file
*/
function getFileObject($path)
{
	$pathinfo = pathinfo($path);
	$filename = $pathinfo["basename"];
	$displayName = $filename; //to be updated in the future
	
	$streamers = array();
	
	foreach(getAvailableStreamers($path) as $s)
	{
		$streamers[] = array("extension" => $s->toExt, "streamerID" => $s->id, "mediaType" => $s->outputMediaType);
	}
	return array(
		"filename" 		=> $filename,
		"displayName"	=> $displayName,
		"streamers"		=> $streamers,
		
	);
}
?>