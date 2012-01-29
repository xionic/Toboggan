<?php
require_once("classes/Streamer.class.php");


/**
* returns streamer profiles which are suitable to produce streams for the given file
*/
function getAvailableStreamers($file){
	//get file extension
	$pathinfo = pathinfo($file);
	$extension = $pathinfo["extension"];
	
	//array to be filled with streamer settings appropriate to this file	
	$suitableStreamers = array();
	
	//find suitable streamers for extension
	global $config;
	foreach($config["videoStreamers"] as $item){
		if($extension == $item["fromExt"]){
			//construct Streamer objects
			$suitableStreamers[] = new Streamer($item["id"], $item["fromExt"], $item["toExt"],$item["cmd"],$item["mime"],$item["outputMediaType"], $item["bitrateCmd"]);
		}
	}
	//if no streamers available for file
	if(count($suitableStreamers)==0)
		return false;
	
	return $suitableStreamers;

}

/**
* get a streamer profile from its id
*/

function getStreamerById($id){
	global $config;
	foreach($config["videoStreamers"] as $item){
		if($id == $item["id"]){
			return new Streamer($item["id"], $item["fromExt"], $item["toExt"],$item["cmd"],$item["mime"],$item["outputMediaType"], $item["bitrateCmd"]);
		}
	}
	return false;
}

function getCurrentMediaDir(){
	global $config;
	return $config["basedir"];
}


?>