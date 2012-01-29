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

function getDBConnection()
{
	$db = new PDO(PDO_DSN);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $db;
}

/**
* get mediaSource path from it's ID
*/
function getMediaSourcePath($mediaSourceID)
{
	$conn = getDBConnection();
	$stmt = $conn->prepare("SELECT path FROM mediaSource WHERE idmediaSource = :idmediaSource");
	$stmt->bindValue(":idmediaSource",$mediaSourceID, PDO::PARAM_INT);
	$stmt->execute();

	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	return $row["path"];
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
		$streamers[] = array("extension" => $s->toExt, "streamerID" => $s->id);
	}
	return array(
		"filename" 		=> $filename,
		"displayName"	=> $displayName,
		"streamers"		=> $streamers,
		
	);
}


?>