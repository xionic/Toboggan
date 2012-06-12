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
* get a user's info
*/
function getUserInfo($username)
{
	$conn = getDBConnection();

	$stmt = $conn->prepare("SELECT * FROM User WHERE username=:username;");
	$stmt->bindValue(":username",$username,PDO::PARAM_STR);
	$stmt->execute();

	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	closeDBConnection($conn);
	
	return(isset($rows[0])?$rows[0]:null);
}
/**
* Get a user's info from an id
*/
function getUserInfoFromID($userid)
{	
	$conn = getDBConnection();

	$stmt = $conn->prepare("SELECT * FROM User WHERE idUser=:idUser;");
	$stmt->bindValue(":idUser",$userid,PDO::PARAM_INT);
	$stmt->execute();

	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	closeDBConnection($conn);
	
	return(isset($rows[0])?$rows[0]:null);
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
* outputs a JSON object of a all the userid and usernames
*/
function outputUserList_JSON()
{
	$users = getUsers();
	restTools::sendResponse(json_encode($users), 200, "text/json");
}

/**
* returns an array of users with userid and username
*/
function getUsers()
{	
	$conn = getDBConnection();
	
	$stmt = $conn->prepare("SELECT idUser, username FROM User");
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	return $results;
}
/**
* outputs a json representation of a user
*/
function outputUserSettings_JSON($userid)
{
	$user = getUserObject($userid);
	restTools::sendResponse(json_encode($user), 200, "text/json");
}
/**
* returns an array representing a user
*/
function getUserObject($userid)
{
	$conn = getDBConnection();
	$conn->beginTransaction();
	
	//assemble user info
	$stmt = $conn->prepare("
		SELECT idUser, username, email, enabled, maxAudioBitrate, maxVideoBitrate, maxBandwidth,
				enableTrafficLimit, trafficLimit, trafficLimitPeriod
			FROM User
			WHERE idUser = :userid");
	$stmt->bindValue(":userid", $userid, PDO::PARAM_INT);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$userObj = $results[0];
	
	//assemble user permissions info
	//get  permissions for "normal" action - ie those with not targetObjectID
	$stmt = $conn->prepare("
		SELECT Action.idAction as id, displayName as displayName, CASE WHEN idUserPermission IS NOT NULL THEN 'Y' ELSE 'N' END as granted 
			FROM Action CROSS JOIN User 
				LEFT JOIN (
					SELECT * 
						FROM UserPermission 
						WHERE idUser = :userid
				) as UP 
				USING (idAction) 
			WHERE User.idUser = :userid 
				AND targetObjectID IS NULL;
		;
	");
	$stmt->bindValue(":userid", $userid, PDO::PARAM_INT);
	$stmt->bindValue(":userid", $userid, PDO::PARAM_INT);
	$stmt->execute();
	$userStandardPerms = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$userObj["permissions"]["actions"] = $userStandardPerms;
	
	//get  permissions for accessMediaSource action - special case for accessing certain mediaSources
	$stmt = $conn->prepare("
		SELECT mediaSource.idmediaSource as id, mediaSource.displayName as displayName, 
		CASE WHEN PermissionAction.idAction IS NOT NULL THEN 'Y' ELSE 'N' END as granted
			FROM mediaSource 
				CROSS JOIN User 
				LEFT JOIN (
					SELECT * FROM UserPermission 
							INNER JOIN Action USING(idAction) 
						WHERE Action.actionName='accessMediaSource') AS PermissionAction 
				ON (PermissionAction.targetObjectID = mediaSource.idmediaSource 
					AND PermissionAction.idUser=User.idUser) 
			WHERE User.idUser= :userid;
		
	");
	$stmt->bindValue(":userid", $userid, PDO::PARAM_INT);
	$stmt->execute();
	$userMediaSourcePerms = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$userObj["permissions"]["mediaSources"] = $userMediaSourcePerms;
	
	//get  permissions for accessStreamer action - special case for accessing certain streamers
	$stmt = $conn->prepare("
		SELECT idextensionMap as id, (fromExt.Extension || ' -> '  || toExt.Extension) as displayName, 
		CASE WHEN PermissionAction.idAction IS NOT NULL THEN 'Y' ELSE 'N' END as granted
			FROM extensionMap
				INNER JOIN fromExt USING(idfromExt)
				INNER JOIN toExt USING(idtoExt)
				CROSS JOIN User 
				LEFT JOIN (
					SELECT * FROM UserPermission 
							INNER JOIN Action USING(idAction) 
						WHERE Action.actionName='accessStreamer') AS PermissionAction 
				ON (PermissionAction.targetObjectID = extensionMap.idextensionMap 
					AND PermissionAction.idUser=User.idUser) 
			WHERE User.idUser= :userid;
		
	");
	$stmt->bindValue(":userid", $userid, PDO::PARAM_INT);
	$stmt->execute();
	$userStreamerPerms = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$userObj["permissions"]["streamers"] = $userStreamerPerms;
	
	
	$conn->commit();	
	closeDBConnection($conn);

	return $userObj;
}

/**
* updates an existing user's settings
*/
function updateUser($userid, $json_settings){

	$userSettings = json_decode($json_settings,true);

	$av = new ArgValidator("handleArgValidationError");

	$av->validateArgs($userSettings, array(
		"username"				=> array("string", "notblank"),
		"email"					=> array("string"),
		"enabled"				=> array("int"),
		"maxAudioBitrate"		=> array("int"),
		"maxVideoBitrate"		=> array("int"),
		"maxBandwidth"			=> array("int"),
		"enableTrafficLimit"	=> array("int"),
		"trafficLimit"			=> array("int"),		
		"trafficLimitPeriod"	=> array("int"),		
		"permissions"			=> array("array"),
	));
	$av->validateArgs($userSettings["permissions"], array(
		"actions"				=> array("array"),
		"mediaSources"			=> array("array"),
		"streamers"				=> array("array"),
	));
	foreach($userSettings["permissions"]["actions"] as $perm)
	{
		$av->validateArgs($perm, array(
			"id"				=> array("int"),
			"granted"			=> array("string", "notblank"),
		));
	}
	foreach($userSettings["permissions"]["mediaSources"] as $perm)
	{
		$av->validateArgs($perm, array(
			"id"				=> array("int"),
			"granted"			=> array("string", "notblank"),
		));
	}
	foreach($userSettings["permissions"]["streamers"] as $perm)
	{
		$av->validateArgs($perm, array(
			"id"				=> "int",
			"granted"				=> "string", "notblank",
		));
	}
	
	$conn = null;
	
	$conn = getDBConnection();
	$conn->beginTransaction();
	
	//update user settings
	$stmt = $conn->prepare("UPDATE User SET 
		username = :username,
		email = :email,
		enabled = :enabled,
		maxAudioBitrate = :maxAudioBitrate,
		maxVideoBitrate = :maxVideoBitrate,
		maxBandwidth = :maxBandwidth,
		enableTrafficLimit = :enableTrafficLimit,
		trafficLimit = :trafficLimit,
		trafficLimitPeriod = :trafficLimitPeriod,
		trafficUsed = 0,
		trafficLimitStartTime = strftime('%s','now')
		WHERE idUser = :idUser
		
	");
	
	$stmt->bindValue(":idUser", $userid, PDO::PARAM_INT);
	$stmt->bindValue(":username", $userSettings["username"], PDO::PARAM_STR);
	$stmt->bindValue(":email", $userSettings["email"], PDO::PARAM_STR);
	$stmt->bindValue(":enabled", $userSettings["enabled"], PDO::PARAM_INT);
	$stmt->bindValue(":maxAudioBitrate", $userSettings["maxAudioBitrate"], PDO::PARAM_INT);
	$stmt->bindValue(":maxVideoBitrate", $userSettings["maxVideoBitrate"], PDO::PARAM_INT);
	$stmt->bindValue(":maxBandwidth", $userSettings["maxBandwidth"], PDO::PARAM_INT);
	$stmt->bindValue(":enableTrafficLimit", $userSettings["enableTrafficLimit"], PDO::PARAM_INT);
	$stmt->bindValue(":trafficLimit", $userSettings["trafficLimit"], PDO::PARAM_INT);
	$stmt->bindValue(":trafficLimitPeriod", $userSettings["trafficLimitPeriod"], PDO::PARAM_INT);
	$stmt->execute();
		
	//update user permissions	
	//build a normalised permissions structure to match the db structure (i.e. merge in the special cases [mediaSources and streamers])
	$newUserPermissions = array();
	//action permissions
	foreach($userSettings["permissions"]["actions"] as $perm)
	{
		if($perm["granted"] == 'Y')
			$newUserPermissions[] = array("userid" => $userid, "actionid" => $perm["id"]);
	}
	
	//mediaSource permissions
	foreach($userSettings["permissions"]["mediaSources"] as $perm)
	{
		if($perm["granted"] == 'Y')
			$newUserPermissions[] = array("userid" => $userid, "actionid" => PERMISSION_ACCESSMEDIASOURCE, "targetObjectID" => $perm["id"]);
	}
	
	//streamer permissions
	foreach($userSettings["permissions"]["streamers"] as $perm)
	{
		if($perm["granted"] == 'Y')
			$newUserPermissions[] = array("actionid" => PERMISSION_ACCESSSTREAMER,  "targetObjectID" => $perm["id"]);
	}

	//remove existing permission for the user
	$stmt = $conn->prepare("DELETE FROM UserPermission WHERE idUser = :userid");
	$stmt->bindValue(":userid", $userid, PDO::PARAM_INT);
	$stmt->execute();
	
	//insert new permissions
	foreach($newUserPermissions as $perm)
	{
		$stmt = $conn->prepare(
			"INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(:userid, :actionid, "
				. (isset($perm["targetObjectID"])?":targetObjectID":"NULL") 
			.")"
		);
		
		$stmt->bindValue(":userid", $userid, PDO::PARAM_INT);
		$stmt->bindValue(":actionid", $perm["actionid"], PDO::PARAM_INT);
		if(isset($perm["targetObjectID"]))
			$stmt->bindValue(":targetObjectID", $perm["targetObjectID"], PDO::PARAM_INT);
		$stmt->execute();
	}
	
	$conn->commit();		
	
	closeDBConnection($conn);
}

/**
* adds a new user given settings
*/
function addUser($json_settings)
{

	$userSettings = json_decode($json_settings,true);
	
	$av = new ArgValidator("handleArgValidationError");
	
	$av->validateArgs($userSettings, array(
		"username"				=> array("string", "notblank"),
		"password"				=> array("string", "notblank"),
		"email"					=> array("string"),
		"enabled"				=> array("int"),
		"maxAudioBitrate"		=> array("int"),
		"maxVideoBitrate"		=> array("int"),
		"maxBandwidth"			=> array("int"),
	));
	
	$conn = null;
	
	$conn = getDBConnection();
	$conn->beginTransaction();
	
	$stmt = $conn->prepare("INSERT INTO User(username, password, email, enabled, maxAudioBitrate, maxVideoBitrate, maxBandwidth) VALUES
		(
			:username,
			:password,
			:email,
			:enabled,
			:maxAudioBitrate,
			:maxVideoBitrate,
			:maxBandwidth
		)
	");
	
	$stmt->bindValue(":username", $userSettings["username"], PDO::PARAM_STR);
	$stmt->bindValue(":password", userLogin::hashPassword($userSettings["password"]), PDO::PARAM_STR);
	$stmt->bindValue(":email", $userSettings["email"], PDO::PARAM_STR);
	$stmt->bindValue(":enabled", $userSettings["enabled"], PDO::PARAM_INT);
	$stmt->bindValue(":maxAudioBitrate", $userSettings["maxAudioBitrate"], PDO::PARAM_INT);
	$stmt->bindValue(":maxVideoBitrate", $userSettings["maxVideoBitrate"], PDO::PARAM_INT);
	$stmt->bindValue(":maxBandwidth", $userSettings["maxBandwidth"], PDO::PARAM_INT);
	$stmt->execute();
	
	$conn->commit();
	
	closeDBConnection($conn);
	
}

/**
* removes a user's account 
*/
function deleteUser($userid)
{
	$conn = null;
	
	$conn = getDBConnection();
	$conn->beginTransaction();
	
	//remove client settings for the user 
	$stmt = $conn->prepare("DELETE FROM ClientSettings WHERE idUser = :idUser");		
	$stmt->bindValue(":idUser", $userid, PDO::PARAM_INT); 
	$stmt->execute();
	
	//remove the user
	$stmt = $conn->prepare("DELETE FROM User WHERE idUser = :idUser");		
	$stmt->bindValue(":idUser", $userid, PDO::PARAM_INT); 
	$stmt->execute();
	
	$conn->commit();
		
	closeDBConnection($conn);
}

/**
* update a user's password
*/
function changeUserPassword($userid, $password)
{
	$conn = null;
	$conn = getDBConnection();
	$conn->beginTransaction();
	
	$stmt = $conn->prepare("UPDATE User SET Password = :password WHERE idUser = :idUser");
	
	$stmt->bindValue(":idUser", $userid, PDO::PARAM_INT);
	$stmt->bindValue(":password", userLogin::hashPassword($password), PDO::PARAM_STR);

	$stmt->execute();
	
	$conn->commit();
		
	closeDBConnection($conn);
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
/**
* checks whether the currently auth'd user has permission to perform the action given by actionName
*/
function checkUserPermission($actionName, $targetObjectID = false)
{
	appLog(
		"Checking userid '" . userLogin::getCurrentUserID() . "' has permission for action '" . 
		$actionName . "' with targetObjectID '" . $targetObjectID . "'", appLog_DEBUG
	);
	$userID = userLogin::getCurrentUserID();
	$sql = "SELECT 1 
			FROM UserPermission 
			INNER JOIN Action USING(idAction)
			INNER JOIN User USING(idUser)
			WHERE 
				`User`.idUser = :idUser
			AND
				actionName = :actionName
			";
	if($targetObjectID !== false)
		$sql .= "AND targetObjectID = :targetObjectID";

	$conn = getDBConnection();	
	$conn->beginTransaction();
	$stmt = $conn->prepare($sql);	
	
	$stmt->bindValue("idUser", $userID, PDO::PARAM_INT);
	$stmt->bindValue("actionName", $actionName, PDO::PARAM_STR);
	if($targetObjectID !== false)
		$stmt->bindValue("targetObjectID", $targetObjectID);
		
	$stmt->execute();
	
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$conn->commit();
	closeDBConnection($conn);	
	return (count($results)==0?false:true);
}

/**
* Returns the number of KB remaining of the user's traffic limit
*/
function getRemainingUserTrafficLimit($userid)
{
	//check if the limit is due to be reset and reset it if needed
	resetUserTrafficLimitIfNeeded($userid);
	
	$conn = getDBConnection();	
	$conn->beginTransaction();
	
	$stmt = $conn->prepare("SELECT enableTrafficLimit, (trafficLimit - trafficUsed) as remainingTraffic FROM User WHERE idUser = :userid");	
	
	$stmt->bindValue("userid", $userid, PDO::PARAM_INT);
		
	$stmt->execute();
	
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$conn->commit();
	closeDBConnection($conn);	

	//check that the user actually has a limit
	if(count($results) == 0  || $results[0]["enableTrafficLimit"] == 0)
		return false;
	else 
		return (int)$results[0]["remainingTraffic"];
}

/**
* Update the used traffic of the given user. Add $KBUsed KB to the used amount.
* NOTE: This function does not call resetUserTrafficLimitIfNeeded since the traffic
* used should not be updated (i.e. more traffic used) without first checking the remaining traffic by calling getRemainingUserTrafficLimit which does call
* resetUserTrafficLimitIfNeeded
*/
function updateUserUsedTraffic($userid, $KBUsed)
{
	$conn = getDBConnection();	
	$conn->beginTransaction();
	
	$stmt = $conn->prepare("UPDATE User SET trafficUsed = (trafficUsed + :kbused) WHERE idUser = :userid");	
	
	$stmt->bindValue("userid", $userid, PDO::PARAM_INT);
	$stmt->bindValue("kbused", $KBUsed, PDO::PARAM_INT);
		
	$stmt->execute();

	$conn->commit();
	closeDBConnection($conn);
}

/**
* Checks if a user's traffic limit period has elapsed and resets the limit if it has
*/
function resetUserTrafficLimitIfNeeded($userid)
{
	$conn = getDBConnection();	
	$conn->beginTransaction();
	
	$stmt = $conn->prepare("SELECT enableTrafficLimit, (trafficLimitPeriod - (strftime('%s','now') - (trafficLimitStartTime))) as remainingPeriod FROM User WHERE idUser = :userid");	
	
	$stmt->bindValue("userid", $userid, PDO::PARAM_INT);
		
	$stmt->execute();

	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	//check that the user actually has a limit
	if(count($results) > 0 && $results[0]["enableTrafficLimit"] == 1)
	{
		$remainingPeriod = $results[0]["remainingPeriod"];
		if($remainingPeriod <= 0) 
		{
			appLog("Reseting traffic limit for user with id: " . userLogin::getCurrentUserID() . ". The period expired ". ($remainingPeriod*-1) . " seconds ago" ,appLog_DEBUG);
			$stmt = $conn->prepare("UPDATE User SET trafficLimitStartTime = strftime('%s','now'), trafficUsed = 0 WHERE idUser = :userid");		
			$stmt->bindValue("userid", $userid, PDO::PARAM_INT);				
			$stmt->execute();
		}		
	}
		

	$conn->commit();
	closeDBConnection($conn);
	
}

function outputUserTrafficLimitStats_JSON($userid)
{ 
	return restTools::sendResponse(json_encode(getUserTrafficLimitStats($userid),200));
}

function getUserTrafficLimitStats($userid)
{
	//check if the limit is due to be reset and reset it if needed
	resetUserTrafficLimitIfNeeded($userid);

	$conn = getDBConnection();	
	$conn->beginTransaction();
	
	$stmt = $conn->prepare("
		SELECT enableTrafficLimit, trafficLimit, trafficUsed, trafficLimitPeriod, (trafficLimitPeriod - (strftime('%s','now') - (trafficLimitStartTime))) as timeToReset 
			FROM User 
			WHERE idUser = :userid");	
	
	$stmt->bindValue("userid", $userid, PDO::PARAM_INT);
		
	$stmt->execute();

	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);	

	$conn->commit();
	closeDBConnection($conn);
	
	
	//check that the user actually has a limit
	if(count($results) == 0  || $results[0]["enableTrafficLimit"] == 0)
	{
		$returnVal["enableTrafficLimit"] = 0;
	}
	else
	{
		$returnVal = $results[0];
	}
	
	return $returnVal;
}

?>