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
	
	$suitableStreamers = array();
	foreach($results as $row){
		//check that the user has permission
		if(checkUserPermission("accessStreamer", $row["idextensionMap"]))
			$suitableStreamers[] = new Streamer($row["idextensionMap"], $row["fromExt"], $row["toExt"],$row["command"],$row["MimeType"],$row["MediaType"], $row["bitrateCmd"]);
	}	
	
		
	
	return $suitableStreamers;
}

/**
* get a streamer profile from its id
*/
function getStreamerById($id){
	
	$conn = getDBConnection();
	
	$stmt = $conn->prepare("SELECT idextensionMap, `fromExt`.Extension as fromExt, `toExt`.Extension as toExt, command, MimeType , MediaType, bitrateCmd
				FROM extensionMap 
				INNER JOIN fromExt USING (idfromExt)
				INNER JOIN toExt USING(idtoExt)
				INNER JOIN transcode_cmd USING(idtranscode_cmd)
				WHERE idextensionMap = :idextensionMap");
	$stmt->bindValue(":idextensionMap",$id, PDO::PARAM_INT);
	$stmt->execute();

	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	$stmt->closeCursor();
	closeDBConnection($conn);
		
		
	if($row && checkUserPermission("accessStreamer", $row["idextensionMap"])) //check user has permission to use the streamer too
		return new Streamer($row["idextensionMap"], $row["fromExt"], $row["toExt"],$row["command"],$row["MimeType"],$row["MediaType"], $row["bitrateCmd"]);
	else
		return false;
	
}
/**
*
*/
function getAllStreamers()
{
	$conn = getDBConnection();
	
	$stmt = $conn->prepare("SELECT idextensionMap, `fromExt`.Extension as fromExt, `toExt`.Extension as toExt, command, MimeType , MediaType, bitrateCmd FROM extensionMap 
				INNER JOIN fromExt USING (idfromExt)
				INNER JOIN toExt USING(idtoExt)
				INNER JOIN transcode_cmd USING(idtranscode_cmd)
				");
	$stmt->execute();

	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$stmt->closeCursor();
	closeDBConnection($conn);
		
	if($rows)
	{
		$streamers = array();
		foreach($rows as $row)
		{
			if(checkUserPermission("accessStreamer",$row["idextensionMap"]))//check user has permission to access the streamer
				$streamers[] =  new Streamer($row["idextensionMap"], $row["fromExt"], $row["toExt"],$row["command"],$row["MimeType"],$row["MediaType"], $row["bitrateCmd"]);
		}
		return $streamers;
	}
	else
		return false;
}

/**
* get a streamer profile from the fromExtension and toExtension
*/
function getStreamerByExtensions($fromExt, $toExt)
{
	$conn = getDBConnection();
	
	$stmt = $conn->prepare("SELECT idextensionMap, `fromExt`.Extension as fromExt, `toExt`.Extension as toExt, command, MimeType , MediaType, bitrateCmd FROM extensionMap 
				INNER JOIN fromExt USING (idfromExt)
				INNER JOIN toExt USING(idtoExt)
				INNER JOIN transcode_cmd USING(idtranscode_cmd)
				WHERE 
					`fromExt`.Extension = :fromExt
					AND `toExt`.Extension = :toExt
				");
	$stmt->bindValue(":fromExt",$fromExt, PDO::PARAM_STR);
	$stmt->bindValue(":toExt",$toExt, PDO::PARAM_STR);
	$stmt->execute();

	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	$stmt->closeCursor();
	closeDBConnection($conn);
	
	if($row &&checkUserPermission("accessStreamer",$row["idextensionMap"]))//check user has permission to access the streamer)
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
* checks that the code and the DB are using the same DB schema version - returns boolean
*/
function validateDBVersion()
{
	$conn = getDBConnection();
	$stmt = $conn->prepare("SELECT version FROM schema_information");
	$stmt->execute();

	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	closeDBConnection($conn);
	
	return $row["version"] == DB_VERSION;
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
	//check user is allowed to access this media Source
	checkActionAllowed("accessMediaSource",$mediaSourceID);
	
	$conn = getDBConnection();
	$stmt = $conn->prepare("SELECT path FROM mediaSource WHERE idmediaSource = :idmediaSource");
	$stmt->bindValue(":idmediaSource",$mediaSourceID, PDO::PARAM_INT);
	$stmt->execute();

	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	closeDBConnection($conn);	
	return $row["path"];	
}
/**
* get an array of media sources
*/
function getMediaSources(){
	$conn = null;
	
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
	closeDBConnection($conn);
	
	//check user has permission for each mediaSource
	$returnMediaSources = array();
	foreach($mediaSources as $mediaSource)
	{
		if(checkUserPermission("accessMediaSource", $mediaSource["mediaSourceID"]))
		{
			$returnMediaSources[] = $mediaSource;
		}
	}
	return $returnMediaSources;
}

/**
* outputs a json encoded string representing a list of media sources
*/
function outputMediaSourcesList_JSON(){
	restTools::sendResponse(json_encode(getMediaSources(),200));
}

/**
* get the current maximum bandwidth that media should be streamed at for the current user
*/
function getCurrentMaxBandwidth(){
	$userid = userLogin::getCurrentUserID();
	
	$conn = getDBConnection();
	$stmt = $conn->prepare("SELECT maxBandwidth FROM User WHERE idUser = :idUser");
	$stmt->bindValue(":idUser",$userid, PDO::PARAM_INT);
	$stmt->execute();

	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	closeDBConnection($conn);
	
	return $result["maxBandwidth"];	
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
	
	$conn = getDBConnection();
	$stmt = $conn->prepare("SELECT $mediaColumn FROM User WHERE idUser = :idUser ");
	$stmt->bindValue(":idUser",$userid, PDO::PARAM_INT);
	$stmt->execute();

	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	closeDBConnection($conn);
	
	return $result[$mediaColumn]."k"; // whack a k on the end for kilobytes	
}
/**
* check an api key is valid
*/
function checkAPIKey($apikey)
{
	$conn = getDBConnection();
	$stmt = $conn->prepare("SELECT 1 FROM APIKey WHERE apikey = :apikey ");
	$stmt->bindValue("apikey",$apikey, PDO::PARAM_STR);
	$stmt->execute();

	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	closeDBConnection($conn);
	return $result ? true:false;	
}


/**
* Save a client's settings blob
*/
function saveClientSettings($settings, $apikey, $userid)
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

function getClientSettings($apikey, $userid){
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

/**
* output a JSON object representing all changable server settings
*/
function outputStreamerSettings_JSON()
{
	$settings = getStreamerSettings();
	restTools::sendResponse(json_encode($settings), 200, "text/json");
}
/**
* get an object representing the server settings
*/
function getStreamerSettings()
{
	//results structure
	$results = array();
	
	//db connection
	$conn = getDBConnection();
		
	//get streamer settings
	$stmt = $conn->prepare("SELECT DISTINCT fromExt.bitrateCmd,
		toExt.Extension AS toExtension, toExt.MimeType, toExt.MediaType, transcode_cmd.command
		FROM fromExt
		INNER JOIN extensionMap USING(idfromExt)
		INNER JOIN toExt USING(idtoExt)
		INNER JOIN transcode_cmd USING(idtranscode_cmd);
	");
	$stmt->execute();
	
	//get streamer results and for each toext query which from ext go to it to aggregate
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach($rows as &$streamer)
	{
		$stmt = $conn->prepare("SELECT fromExt.Extension as fromExtension
			FROM fromExt 
			INNER JOIN ExtensionMap USING(idfromExt)
			INNER JOIN toExt USING(idtoExt)
			WHERE toExt.Extension = :toExt
		");
		$stmt->bindValue(":toExt", $streamer["toExtension"]);
		$stmt->execute();
		
		$fromExtStr = "";
		while(($fromExt = $stmt->fetch(PDO::FETCH_ASSOC)) !== false){
			$fromExtStr .= $fromExt["fromExtension"] . ",";
		}
		$fromExtStr = substr($fromExtStr, 0, -1); 
		
		//update main resuklt set
		$streamer["fromExtensions"] = $fromExtStr;
		
	}		
	closeDBConnection($conn);
	
	return $rows;
}

function saveStreamerSettings($settings_JSON)
{

	appLog("Saving new server settings", appLog_VERBOSE);
	//get object
	$settings = json_decode($settings_JSON, true);
	
	//validate the bitch!
	//basic validation
	$av = new ArgValidator("handleArgValidationError");
		
	//validate streamer section
	foreach($settings as $streamer)
	{
		$av->validateArgs($streamer, array(
			"fromExtensions"	=>		array("string", "notblank"),
			"bitrateCmd"		=>		array("string", "notblank"),
			"toExtension"		=>		array("string", "notblank"),
			"MimeType"			=>		array("string", "notblank"),
			"MediaType"			=>		array("string", "notblank"),
			"command"			=>		array("string", "notblank"),
		));
	}
	//TODO - add more validation - deduplication etc
	
	//explode fromExt grouping for db entry
	$expandedStreamers = array();
	foreach($settings as $streamer)
	{
		$fromExtArr = explode(",", $streamer["fromExtensions"]);
		//loop through each fromext and create a copy of the streamer for it
		//var_dump_pre($fromExtArr);
		foreach($fromExtArr as $fromExt)
		{
			$expandedStreamers[] = new Streamer(
				null,
				$fromExt,
				$streamer["toExtension"],
				$streamer["command"],
				$streamer["MimeType"],
				$streamer["MediaType"],
				$streamer["bitrateCmd"]
			);
		}		
	}
	
	//replace old streamers with explanded ones
	$settings = $expandedStreamers;
	
	$conn = null;

	//start the transaction
	$conn = getDBConnection();
	$conn->beginTransaction();
	
	//remove streamers that are to be deleted
	$DBstreamers = getAllStreamers();
	$streamerIDsToRemove = array();
	foreach($DBstreamers as $DBStreamer)
	{
		foreach($settings as $newStreamer)
		{
			if($DBStreamer->fromExt == $newStreamer->fromExt && $DBStreamer->toExt == $newStreamer->toExt)
				continue 2;
		}
		$streamerIDsToRemove[] = $DBStreamer->id;
	}
	
	//do the removing from the DB
	foreach($streamerIDsToRemove as $id)
	{
		removeStreamer($conn, $id);
	}
	
	//prepare the data to be inserted	
	
	//loop through the streamers and check for changes and additions
	foreach($settings as $newStreamer)
	{
		$DBStreamer = null;
		
		if(($DBStreamer = getStreamerByExtensions($newStreamer->fromExt, $newStreamer->toExt)) !== false)
		{//existing streamer to update
			//port existing id into the newStreamer's class to simply update the existing record
			$newStreamer->id = $DBStreamer->id;
			
			updateStreamer($conn, $newStreamer);
		}
		else
		{//new streamer to add
			insertStreamer($conn, $newStreamer);
		}
	}
	
	$conn->commit();
	
	closeDBConnection($conn);

}
/**
 * updates a streamer - (has to work inside an existing transaction, hence the $conn)
*/
function updateStreamer($conn, $streamer)
{
	appLog("Updating streamer mapping in DB with id {$streamer->id}", appLog_VERBOSE);
	
	appLog("Updating fromExt with new settings for  ({$streamer->fromExt})", appLog_DEBUG);
	
	//update fromExt
	$stmt = $conn->prepare("
		 UPDATE fromExt 
		 SET Extension = :fromExt, bitrateCmd = :bitrateCmd
		 WHERE idfromExt = 
		(
			SELECT idfromExt 
			FROM extensionMap 
			WHERE idExtensionMap = :idExtMap
		)");
	
	
	$stmt->bindValue(":fromExt", $streamer->fromExt);
	$stmt->bindValue(":bitrateCmd", $streamer->bitrateCmd);
	$stmt->bindValue(":idExtMap", $streamer->id);
	$stmt->execute();
	
	appLog("Updating toExt with new settings for ({$streamer->fromExt})", appLog_DEBUG);
	//update toExt
	$stmt = $conn->prepare("
		 UPDATE toExt 
		 SET Extension = :toExt, MimeType = :mime, MediaType = :mediatype
		 WHERE idtoExt = 
		(
			SELECT idtoExt 
			FROM extensionMap 
			WHERE idExtensionMap = :idExtMap
		)");
	$stmt->bindValue(":toExt", $streamer->toExt);
	$stmt->bindValue(":mime", $streamer->mime);
	$stmt->bindValue(":mediatype", $streamer->outputMediaType);
	$stmt->bindValue(":idExtMap", $streamer->id);
	$stmt->execute();
	
	appLog("Updating transcode_cmd with new settings for ({$streamer->fromExt})", appLog_DEBUG);
	//update transcode_cmd
	$stmt = $conn->prepare("
		 UPDATE transcode_cmd 
		 SET command = :command
		 WHERE idtranscode_cmd = 
		(
			SELECT idtranscode_cmd 
			FROM extensionMap 
			WHERE idExtensionMap = :idExtMap
		)");
	
	$stmt->bindValue(":command", $streamer->cmd);
	$stmt->bindValue(":idExtMap", $streamer->id);
	$stmt->execute();
	
}
/**
 * Inserts a new streamer mapping into the DB
*/
function insertStreamer($conn, $streamer)
{
	appLog("Inserting new streamer mapping to DB", appLog_DEBUG);

	appLog("Inserting (if needed) into fromExt", appLog_DEBUG);
	//add/update fromExt
	$fromExtID = updateOrInsertFromExt($conn, $streamer->fromExt, $streamer-> bitrateCmd);
	
	appLog("Inserting (if needed) into toExt", appLog_DEBUG);	
	//add/update toExt
	$toExtID = updateOrInsertToExt($conn, $streamer->toExt, $streamer->mime, $streamer->outputMediaType);
	
	appLog("Inserting (if needed) into transcode_cmd", appLog_DEBUG);
	//add/update transcodeCmd
	$transcode_cmdID = updateOrInsertTranscodeCmd($conn, $streamer->cmd);
	
	appLog("Inserting new transcode mapping into extensionMap", appLog_DEBUG);
	//extensionMap	
	$stmt = $conn->prepare("INSERT INTO extensionMap(idfromExt, idtoExt, idtranscode_cmd) VALUES(:idfrom, :idto, :idtrans)");
	$stmt->bindValue(":idfrom", $fromExtID);
	$stmt->bindValue(":idto", $toExtID);
	$stmt->bindValue(":idtrans", $transcode_cmdID);
	$stmt->execute();
	
}

/**
* Remove a streamer from the db
*/
function removeStreamer($conn, $streamerID)
{
	appLog("Removing streamer with ID $streamerID from DB", appLog_DEBUG);

	//remove streamer mapping
	$stmt = $conn->prepare("DELETE FROM extensionMap WHERE idextensionMap = :sid");
	$stmt->bindValue(":sid", $streamerID);
	$stmt->execute();

	//clean up orphans
	//fromExt
	$stmt = $conn->query("DELETE FROM fromExt WHERE idfromExt NOT IN (SELECT idfromExt FROM extensionMap);");
	$stmt->execute();
	//toExt
	$stmt = $conn->query("DELETE FROM toExt WHERE idtoExt NOT IN (SELECT idtoExt FROM extensionMap);");
	$stmt->execute();
	//transcode_cmd
	$stmt = $conn->query("DELETE FROM transcode_cmd WHERE idtranscode_cmd NOT IN (SELECT idtranscode_cmd FROM extensionMap);");
	$stmt->execute();

}

/**
* inserts and returns, or just returns the fromext ID depending on whether or not the extension is already in the table
*/
function updateOrInsertFromExt($conn, $fromExtension, $bitrateCmd)
{
	$stmt = $conn->prepare("SELECT idfromExt FROM fromExt WHERE Extension = :fromExt");
	$stmt->bindValue(":fromExt", $fromExtension);
	$stmt->execute();
	
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	$fromExtID = null;
	
	if(!$result) // need to insert
	{
		$stmt = $conn->prepare("INSERT INTO fromExt(Extension, bitrateCmd) VALUES(:ext, :bcmd)");
		$stmt->bindValue(":ext", $fromExtension);
		$stmt->bindValue(":bcmd", $bitrateCmd);
		$stmt->execute();
		$fromExtID = $conn->lastInsertId();
	}
	else
		 $fromExtID = $result["idfromExt"];
	/*else //need to update
	{
		$stmt = $conn->prepare("UPDATE fromExt SET bitrateCmd = :bcmd WHERE Extension = :ext");
		$stmt->bindValue(":ext", $fromExtension);
		$stmt->bindValue(":bcmd", $bitrateCmd);
		$stmt->execute();
		
		$fromExtID = $result[0]["fromextid"]; // id for later from original result
	}*/
	
	return $fromExtID;
}

/**
*inserts and returns, or just returns the toExt id depending on whether or not the extension is already in the table
*/
function updateOrInsertToExt($conn, $toExtension, $mimeType, $mediaType)
{
	$stmt = $conn->prepare("SELECT idtoExt FROM toExt WHERE Extension = :toExt");
	$stmt->bindValue(":toExt", $toExtension);
	$stmt->execute();
	
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	$toExtID = null; //init var
	
	if(!$result) // need to insert
	{
		$stmt = $conn->prepare("INSERT INTO toExt(Extension, MimeType, MediaType) VALUES(:ext, :mime, :mediatype)");
		$stmt->bindValue(":ext", $toExtension);
		$stmt->bindValue(":mime", $mimeType);
		$stmt->bindValue(":mediatype", $mediaType);
		$stmt->execute();
		$toExtID = $conn->lastInsertId();
		
	}
	else //need to update
	{
		$toExtID = $result["idtoExt"];
	/*
		$stmt = $conn->prepare("UPDATE toExt SET MimeType = :mime, MediaType = :mediatype WHERE Extension = :ext");
		$stmt->bindValue(":ext", $toExtension);
		$stmt->bindValue(":mime", $mimeType);
		$stmt->bindValue(":mediatype", $mediaType);
		$stmt->execute();
		$toExtID = $result[0]["idtoExt"]; // id for later from original result*/
	}
	
	return $toExtID;
}

/**
*inserts and returns, or just returns the transcode_cmd id depending on whether or not the command is already in the table
*/
function updateOrInsertTranscodeCmd($conn, $command)
{
	$stmt = $conn->prepare("SELECT idtranscode_cmd FROM transcode_cmd WHERE command = :command");
	$stmt->bindValue(":command", $command);
	$stmt->execute();
	
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	$transcode_cmdID = null; //init var
	
	if(!$result) // need to insert
	{
		$stmt = $conn->prepare("INSERT INTO transcode_cmd(command) VALUES(:command)");
		$stmt->bindValue(":command", $command);
		$stmt->execute();
		$transcode_cmdID = $conn->lastInsertId();
		
	}
	else //need to update
	{
		$transcode_cmdID = $result["idtranscode_cmd"];
	/*
		$stmt = $conn->prepare("UPDATE INTO transcode_cmd(command) VALUES(:command)");
		$stmt->bindValue(":command", $command);
		$stmt->execute();*/
		//nothing to update
	}
	return $transcode_cmdID;
}








/**
* outputs a JSON representation of the media source settings
*/
function outputMediaSourceSettings_JSON()
{
	restTools::sendResponse(json_encode(getMediaSourceSettings(),200));
}

/**
* returns an object representing the media Source Settings
*/
function getMediaSourceSettings(){
	$conn = getDBConnection();		
	$stmt = $conn->prepare("SELECT idmediaSource as mediaSourceID, path, displayName FROM mediaSource");	
	$stmt->execute();
	
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	closeDBConnection($conn);	
	return $results;
}

/**
* takes a JSON encoded string representing an object representing new media source settings to update the old ones.
*/
function saveMediaSourceSettings($settings_JSON)
{
	$settings = json_decode($settings_JSON,true);
	//validate them - may as well loop here and below - this doesn't get done much - and it's not lile this file is MASSIVE already
	foreach($settings as $mediaSource)
	{
		//validate them
		$av = new ArgValidator("handleArgValidationError");
		$mediaSource = $av->validateArgs($mediaSource,array(
			"mediaSourceID"	=>	array("int", "notblank", "optional"),
			"path" 			=>	array("string", "notblank"),
			"displayName" 	=> 	array("string", "notblank"),
		));
	}
	
	$conn = null;
	
	$conn = getDBConnection();
	$conn->beginTransaction();	
	
	
	
	//insert new settings
	//list of mediaSourceIDs that should be in the DB - all others will be removed at the end
	$mediaSourceIDsToKeep = array();
	foreach($settings as $mediaSource)
	{			
		//var_dump_pre(getMediaSourcePath($mediaSource["mediaSourceID"]));
		//see if the mediaSourceID is already in the db
		if(!getMediaSourcePath($mediaSource["mediaSourceID"]))
		{
			//insert a new mediaSource
			appLog("Inserting new Media Source with path " . $mediaSource["path"], appLog_DEBUG);
			
			$stmt = $conn->prepare("INSERT INTO mediaSource(path, displayName) VALUES (:path, :dname)");		
			$stmt->bindValue(":path", $mediaSource["path"], PDO::PARAM_STR); 
			$stmt->bindValue(":dname", $mediaSource["displayName"], PDO::PARAM_STR); 
			$stmt->execute();
			$mediaSourceIDsToKeep[] = $conn->lastInsertId();
		}
		else
		{
			//update a current mediaSource
			appLog("Updating Media Source with id " . $mediaSource["mediaSourceID"], appLog_DEBUG);
			
			$stmt = $conn->prepare("UPDATE mediaSource SET path = :path, displayName = :dname WHERE idmediaSource = :mediaSourceID");		
			$stmt->bindValue(":path", $mediaSource["path"], PDO::PARAM_STR);
			$stmt->bindValue(":dname", $mediaSource["displayName"], PDO::PARAM_STR); 
			$stmt->bindValue(":mediaSourceID", $mediaSource["mediaSourceID"], PDO::PARAM_STR); 
			$stmt->execute();
			$mediaSourceIDsToKeep[] = $mediaSource["mediaSourceID"];
		}
		
	}
	
	//now remove the ones that weren't in the passed back list
	//build a query with the correct number of placeholders	
	$tempArr = array_fill(0, count($mediaSourceIDsToKeep), "?")	;
	$query = "DELETE FROM mediaSource WHERE idmediaSource NOT IN (" . implode($tempArr,","). ");";

	
	$stmt = $conn->prepare($query);		
	for($n=0; $n < count($mediaSourceIDsToKeep); $n++)
	{
		$stmt->bindValue($n+1, $mediaSourceIDsToKeep[$n], PDO::PARAM_INT);
	}
	$stmt->execute();
	
	$conn->commit();
		
	closeDBConnection($conn);
}






?>