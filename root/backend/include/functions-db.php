<?php
require_once("classes/Streamer.class.php");

/**
* returns streamer profiles which are suitable to produce streams for the given file
*/
function getAvailableStreamers($file){
	//get file extension
	$pathinfo = pathinfo($file);
	$extension = strtolower($pathinfo["extension"]);
	
	$conn = getDBConnection();
	
	$stmt = $conn->prepare("SELECT idextensionMap, `fromExt`.Extension as fromExt, `toExt`.Extension as toExt, command, MimeType , MediaType, bitrateCmd FROM extensionMap 
				INNER JOIN fromExt USING (idfromExt)
				INNER JOIN toExt USING(idtoExt)
				INNER JOIN transcode_cmd USING(idtranscode_cmd)
				WHERE `fromExt`.Extension = :fromExt");
	$stmt->bindValue(":fromExt",$extension, PDO::PARAM_STR);
	$stmt->execute();
	
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	$suitableStreamers = array();
	foreach($results as $row){
		$suitableStreamers[] = new Streamer($row["idextensionMap"], $row["fromExt"], $row["toExt"],$row["command"],$row["MimeType"],$row["MediaType"], $row["bitrateCmd"]);
	}	
	return $suitableStreamers;
	
	
	
	// //array to be filled with streamer settings appropriate to this file	
	// $suitableStreamers = array();
	
	// //find suitable streamers for extension
	// global $config;
	// foreach($config["videoStreamers"] as $item){
		// if($extension == $item["fromExt"]){
			// //construct Streamer objects
			// $suitableStreamers[] = new Streamer($item["id"], $item["fromExt"], $item["toExt"],$item["cmd"],$item["mime"],$item["outputMediaType"], $item["bitrateCmd"]);
		// }
	// }
	
	// return $suitableStreamers;

}

/**
* get a streamer profile from its id
*/
function getStreamerById($id){
	$conn = getDBConnection();
	
	$stmt = $conn->prepare("SELECT idextensionMap, `fromExt`.Extension as fromExt, `toExt`.Extension as toExt, command, MimeType , MediaType, bitrateCmd FROM extensionMap 
				INNER JOIN fromExt USING (idfromExt)
				INNER JOIN toExt USING(idtoExt)
				INNER JOIN transcode_cmd USING(idtranscode_cmd)
				WHERE idextensionMap = :idextensionMap");
	$stmt->bindValue(":idextensionMap",$id, PDO::PARAM_INT);
	$stmt->execute();

	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	
	
	if($row)
		return new Streamer($row["idextensionMap"], $row["fromExt"], $row["toExt"],$row["command"],$row["MimeType"],$row["MediaType"], $row["bitrateCmd"]);
	else
		return false;
	
}

/**
* get a database connection
*/
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
* get a JSON string representing a list of mediaSources
*/
function getMediaSourceID_JSON(){
$conn = getDBConnection();
	$stmt = $conn->prepare("SELECT idmediaSource, displayName FROM mediaSource");
	$stmt->execute();

	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	$mediaSources = array();
	foreach($results as $row)
	{
		$mediaSources[]  =  array("mediaSourceID" => $row["idmediaSource"], "displayName" => $row["displayName"]);
	}
	return json_encode($mediaSources);
}

/**
* get the current maximum bandwidth that media should be streamed at
*/
function getCurrentMaxBandwidth(){
	return 100;
}

/**
* get the current max bitrate that media should be streamed at
*/
function getCurrentMaxBitrate(){
	return "1910k";
}


?>