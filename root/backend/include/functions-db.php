<?php
require_once("classes/Streamer.class.php");


/**
* returns streamer profiles which are suitable to produce streams for the given file
*/
function getAvailableStreamers($file){
	//get file extension
	$pathinfo = pathinfo($file);
	$extension = strtolower($pathinfo["extension"]);
	
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

function getDBConnection()
{
	$db = new PDO(PDO_DSN);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $db;
}

function getMediaSourcePath($mediaSourceID)
{
	$conn = getDBConnection();
	$stmt = $conn->prepare("SELECT path FROM mediaSource WHERE idmediaSource = :idmediaSource");
	$stmt->bindValue(":idmediaSource",$mediaSourceID, PDO::PARAM_INT);
	$stmt->execute();

	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	return $row["path"];
}


?>