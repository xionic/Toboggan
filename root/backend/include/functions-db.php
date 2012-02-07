<?php
require_once("classes/Streamer.class.php");
require_once("classes/userLogin.class.php");

/**
* returns streamer profiles which are suitable to produce streams for the given file
*/
function getAvailableStreamers($file){
	//get file extension
	$pathinfo = pathinfo($file);
	$extension = strtolower($pathinfo["extension"]);
	
	try
	{
		$conn = getDBConnection();
		
		$stmt = $conn->prepare("SELECT idextensionMap, `fromExt`.Extension as fromExt, `toExt`.Extension as toExt, command, MimeType , MediaType, bitrateCmd FROM extensionMap 
					INNER JOIN fromExt USING (idfromExt)
					INNER JOIN toExt USING(idtoExt)
					INNER JOIN transcode_cmd USING(idtranscode_cmd)
					WHERE `fromExt`.Extension = :fromExt");
		$stmt->bindValue(":fromExt",$extension, PDO::PARAM_STR);
		$stmt->execute();
		
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		closeDBConnection($conn);
	}
	catch (PDOException $e)
	{
		appLog('Connection Failed: '.$e->getMessage(), appLog_INFO);
		return false;
	}
	
	$suitableStreamers = array();
	foreach($results as $row){
		$suitableStreamers[] = new Streamer($row["idextensionMap"], $row["fromExt"], $row["toExt"],$row["command"],$row["MimeType"],$row["MediaType"], $row["bitrateCmd"]);
	}	
	
	return $suitableStreamers;
}

/**
* get a streamer profile from its id
*/
function getStreamerById($id){
	try
	{
		$conn = getDBConnection();
		
		$stmt = $conn->prepare("SELECT idextensionMap, `fromExt`.Extension as fromExt, `toExt`.Extension as toExt, command, MimeType , MediaType, bitrateCmd FROM extensionMap 
					INNER JOIN fromExt USING (idfromExt)
					INNER JOIN toExt USING(idtoExt)
					INNER JOIN transcode_cmd USING(idtranscode_cmd)
					WHERE idextensionMap = :idextensionMap");
		$stmt->bindValue(":idextensionMap",$id, PDO::PARAM_INT);
		$stmt->execute();

		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		closeDBConnection($conn);
	}
	catch (PDOException $e)
	{
		appLog('Connection Failed: '.$e->getMessage(), appLog_INFO);
		return false;
	}	
	
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
	try
	{
		$db = new PDO(PDO_DSN);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $db;
	}
	catch (PDOException $e)
	{
		appLog('Connection Failed: '.$e->getMessage(), appLog_INFO);
		return false;
	}
}

/**
* close a database connection
*/ 
function closeDBConnection($conn)
{
	$conn = null;
}

/**
* get mediaSource path from it's ID
*/
function getMediaSourcePath($mediaSourceID)
{
	try
	{
		$conn = getDBConnection();
		$stmt = $conn->prepare("SELECT path FROM mediaSource WHERE idmediaSource = :idmediaSource");
		$stmt->bindValue(":idmediaSource",$mediaSourceID, PDO::PARAM_INT);
		$stmt->execute();

		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		closeDBConnection($conn);
		
		return $row["path"];
	}
	catch (PDOException $e)
	{
		appLog('Connection Failed: '.$e->getMessage(), appLog_INFO);
		return false;
	}
}
/**
* get an array of media sources
*/
function getMediaSources(){
	try
	{
		$conn = getDBConnection();
		$stmt = $conn->prepare("SELECT idmediaSource, displayName FROM mediaSource");
		$stmt->execute();

		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		closeDBConnection($conn);
		
		$mediaSources = array();
		foreach($results as $row)
		{
			$mediaSources[]  =  array("mediaSourceID" => $row["idmediaSource"], "displayName" => $row["displayName"]);
		}
		return $mediaSources;
	}
	catch (PDOException $e)
	{
		appLog('Connection Failed: '.$e->getMessage(), appLog_INFO);
		return false;
	}
}

/**
* get the current maximum bandwidth that media should be streamed at for the current user
*/
function getCurrentMaxBandwidth(){
	$userid = userLogin::getCurrentUserID();
	try
	{
		$conn = getDBConnection();
		$stmt = $conn->prepare("SELECT maxBandwidth FROM User WHERE idUser = :idUser");
		$stmt->bindValue(":idUser",$userid, PDO::PARAM_INT);
		$stmt->execute();

		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		closeDBConnection($conn);
		
		return $result["maxBandwidth"];
	}
	catch (PDOException $e)
	{
		appLog('Connection Failed: '.$e->getMessage(), appLog_INFO);
		return false;
	}
}

/**
* get the current max bitrate that media should be streamed at dependent on the current user and the media type
*/
function getCurrentMaxBitrate($type){
	//check the media type
	if($type == 'a')
		$mediaColumn = "maxAudioBitrate";
	elseif($type == 'v')
		$mediaColumn = "maxVideoBitrate";
	else
	{
		appLog("Invalid media type given", appLog_INFO);
		return false;
	}
	//get the current user id
	$userid = userLogin::getCurrentUserID();
	try
	{
		$conn = getDBConnection();
		$stmt = $conn->prepare("SELECT $mediaColumn FROM User WHERE idUser = :idUser ");
		$stmt->bindValue(":idUser",$userid, PDO::PARAM_INT);
		$stmt->execute();

		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		closeDBConnection($conn);
		
		return $result[$mediaColumn]."k"; // whack a k on the end for kilobytes
	}
	catch (PDOException $e)
	{
		appLog('Connection Failed: '.$e->getMessage(), appLog_INFO);
		return false;
	}
}
/**
* check an api key is valid
*/
function checkAPIKey($apikey)
{
	try
	{
		$conn = getDBConnection();
		$stmt = $conn->prepare("SELECT 1 FROM APIKey WHERE apikey = :apikey ");
		$stmt->bindValue("apikey",$apikey, PDO::PARAM_STR);
		$stmt->execute();

		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		closeDBConnection($conn);
		return $result ? true:false;
	}
	catch (PDOException $e)
	{
		appLog('Connection Failed: '.$e->getMessage(), appLog_INFO);
		return false;
	}
}
/**
* get a user's info
*/
function getUserInfo($username)
{
	try
	{
		$conn = getDBConnection();

		$stmt = $conn->prepare("SELECT * FROM User WHERE username=:username;");
		$stmt->bindValue(":username",$username,PDO::PARAM_STR);
		$stmt->execute();

		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		closeDBConnection($conn);
	}
	catch (PDOException $e)
	{
		appLog('Connection Failed: '.$e->getMessage(), appLog_INFO);
		return false;
	}
	
	return(isset($rows[0])?$rows[0]:null);
}
/**
* Get a user's info from an id
*/
function getUserInfoFromID($userid)
{	
	try
	{
		$conn = getDBConnection();

		$stmt = $conn->prepare("SELECT * FROM User WHERE idUser=:idUser;");
		$stmt->bindValue(":idUser",$userid,PDO::PARAM_INT);
		$stmt->execute();

		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		closeDBConnection($conn);
	}
	catch (PDOException $e)
	{
		appLog('Connection Failed: '.$e->getMessage(), appLog_INFO);
		return false;
	}
	return(isset($rows[0])?$rows[0]:null);
}

/**
* Save a client's settings blob
*/
function saveClientSettings($settings, $apikey, $userid)
{
	try
	{
		$conn = getDBConnection();

		//check this client and user already have settings saved
		$stmt = $conn->prepare("SELECT idClientSettings FROM ClientSettings INNER JOIN User USING (idUser) INNER JOIN APIKey USING(idAPIKey) WHERE idUser = :idUser AND apikey = :apikey");
		$stmt->bindValue(":idUser",$userid,PDO::PARAM_INT);
		$stmt->bindValue(":apikey",$apikey,PDO::PARAM_STR);
		$stmt->execute();

		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$row)
		{
			$stmt = $conn->prepare("INSERT INTO ClientSettings(idAPIKey, settings, idUser) SELECT idAPIKey, :settings, :idUser FROM APIKey WHERE apikey = :apikey");
			$stmt->bindValue(":idUser",$userid,PDO::PARAM_INT);
			$stmt->bindValue(":settings",$settings,PDO::PARAM_STR);
			$stmt->bindValue(":apikey",$apikey,PDO::PARAM_STR);
			$stmt->execute();
		}
		else
		{
			$stmt = $conn->prepare("UPDATE ClientSettings SET settings = :settings WHERE idClientSettings = :idClientSettings");
			$stmt->bindValue(":idClientSettings",$row["idClientSettings"],PDO::PARAM_INT);
			$stmt->bindValue(":settings",$settings,PDO::PARAM_STR);
			$stmt->execute();
		}

		closeDBConnection($conn);
	}
	catch (PDOException $e)
	{
		appLog('Connection Failed: '.$e->getMessage(), appLog_INFO);
		return false;
	}
}

function getClientSettings($apikey, $userid){
	try
	{
		$conn = getDBConnection();
		
		//check this client and user already have settings saved
		$stmt = $conn->prepare("SELECT settings FROM ClientSettings INNER JOIN User USING (idUser) INNER JOIN APIKey USING(idAPIKey) WHERE idUser = :idUser AND apikey = :apikey");
		$stmt->bindValue(":idUser",$userid,PDO::PARAM_INT);
		$stmt->bindValue(":apikey",$apikey,PDO::PARAM_STR);
		$stmt->execute();	 
		
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		
		closeDBConnection($conn);
		
		if($row)
			return $row["settings"];
		else 
			return false; // no settings
	}
	catch (PDOException $e)
	{
		appLog('Connection Failed: '.$e->getMessage(), appLog_INFO);
		return false;
	}
}


?>