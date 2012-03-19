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
* checks that the code and the DB are using the same DB schema version - returns boolean
*/
function validateDBVersion()
{
	try
	{
		$conn = getDBConnection();
		$stmt = $conn->prepare("SELECT version FROM schema_information");
		$stmt->execute();

		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		closeDBConnection($conn);
		
		return $row["version"] == DB_VERSION;
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
	$conn = null;
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
	closeDBConnection($conn);
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

	try
	{
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
	}
	catch (PDOException $e)
	{
		appLog('Connection Failed: '.$e->getMessage(), appLog_INFO);
		return false;
	}
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
			"fromExtensions"	=>		"string, notblank",
			"bitrateCmd"		=>		"string, notblank",
			"toExtension"		=>		"string, notblank",
			"MimeType"			=>		"string, notblank",
			"MediaType"			=>		"string, notblank",
			"command"			=>		"string, notblank",
		),false);
	}
	//TODO - add more validation
	
	//explode fromExt grouping for db entry
	$expandedStreamers = array();
	foreach($settings as $streamer)
	{
		$fromExtArr = explode(",", $streamer["fromExtensions"]);
		//loop through each fromext and create a copy of the streamer for it
		//var_dump_pre($fromExtArr);
		foreach($fromExtArr as $fromExt)
		{
			$expandedStreamers[] = array(
			"fromExtension"		=>		$fromExt,
			"bitrateCmd"		=>		$streamer["bitrateCmd"],
			"toExtension"		=>		$streamer["toExtension"],
			"MimeType"			=>		$streamer["MimeType"],
			"MediaType"			=>		$streamer["MediaType"],
			"command"			=>		$streamer["command"],
			);
		}		
	}
	//replace old streamers with explanded ones
	$settings = $expandedStreamers;
	
	$conn = null;
	try
	{
		appLog("Clearing old settings from the db", appLog_DEBUG);
		//clear out the db in preparation for the new settings
		$conn = getDBConnection();
		$conn->beginTransaction();
		
		$stmt = $conn->prepare("DELETE FROM extensionMap");
		$stmt->execute();
		
		$stmt = $conn->prepare("DELETE FROM fromExt");
		$stmt->execute();
		
		$stmt = $conn->prepare("DELETE FROM toExt");
		$stmt->execute();
		
		$stmt = $conn->prepare("DELETE FROM transcode_cmd");
		$stmt->execute();
		
		//prepare the data to be inserted
		
		
		appLog("Inserting new settings into the db", appLog_DEBUG);	
		
		//insert the new streamers
		foreach($settings as $streamer)
		{
			updateStreamer($streamer, $conn);		
		}
		
		$conn->commit();
	}
	catch (PDOException $e)
	{
		appLog('Connection Failed: '.$e->getMessage(), appLog_INFO);
		if(isset($conn) && $conn && $conn->inTransaction())
		{
			$conn->rollBack();
		}		
		return false;
	}
	
	closeDBConnection($conn);
}

/**
* update (or inserts) a streamer - (has to work inside an existing transaction, hence the $conn)
*/
function updateStreamer($streamer, $conn)
{
	$stmt = $conn->prepare("SELECT idfromExt FROM fromExt WHERE Extension = :fromExt");
	$stmt->bindValue(":fromExt", $streamer["fromExtension"]);
	$stmt->execute();
	
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$fromExtID = 0; //init var
	
	if(count($result) == 0) // need to insert
	{
		$stmt = $conn->prepare("INSERT INTO fromExt(Extension, bitrateCmd) VALUES(:ext, :bcmd)");
		$stmt->bindValue(":ext", $streamer["fromExtension"]);
		$stmt->bindValue(":bcmd", $streamer["bitrateCmd"]);
		$stmt->execute();
		$fromExtID = $conn->lastInsertId();
	}
	else //need to update
	{
		$stmt = $conn->prepare("UPDATE fromExt SET bitrateCmd = :bcmd WHERE Extension = :ext");
		$stmt->bindValue(":ext", $streamer["fromExtension"]);
		$stmt->bindValue(":bcmd", $streamer["bitrateCmd"]);
		$stmt->execute();
		
		$fromExtID = $result[0]["fromextid"]; // id for later from original result
	}
		
	//toExt
	$stmt = $conn->prepare("SELECT idtoExt FROM toExt WHERE Extension = :toExt");
	$stmt->bindValue(":toExt", $streamer["toExtension"]);
	$stmt->execute();
	
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$toExtID = 0; //init var
	
	if(count($result) == 0) // need to insert
	{
		$stmt = $conn->prepare("INSERT INTO toExt(Extension, MimeType, MediaType) VALUES(:ext, :mime, :mediatype)");
		$stmt->bindValue(":ext", $streamer["toExtension"]);
		$stmt->bindValue(":mime", $streamer["MimeType"]);
		$stmt->bindValue(":mediatype", $streamer["MediaType"]);
		$stmt->execute();
		$toExtID = $conn->lastInsertId();
		
	}
	else //need to update
	{
		$stmt = $conn->prepare("UPDATE toExt SET MimeType = :mime, MediaType = :mediatype WHERE Extension = :ext");
		$stmt->bindValue(":ext", $streamer["toExtension"]);
		$stmt->bindValue(":mime", $streamer["MimeType"]);
		$stmt->bindValue(":mediatype", $streamer["MediaType"]);
		$stmt->execute();
		$toExtID = $result[0]["idtoExt"]; // id for later from original result
	}
	
	//transcodeCmd
	$stmt = $conn->prepare("SELECT idtranscode_cmd FROM transcode_cmd WHERE command = :command");
	$stmt->bindValue(":command", $streamer["command"]);
	$stmt->execute();
	
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$transcode_cmdID = 0; //init var
	
	if(count($result) == 0) // need to insert
	{
		$stmt = $conn->prepare("INSERT INTO transcode_cmd(command) VALUES(:command)");
		$stmt->bindValue(":command", $streamer["command"]);
		$stmt->execute();
		$transcode_cmdID = $conn->lastInsertId();
		
	}
	else //need to update
	{
		$transcode_cmdID = $result[0]["idtranscode_cmd"];
		//nothing to update
	}
	
	//extensionMap
	
	$stmt = $conn->prepare("INSERT INTO extensionMap(idfromExt, idtoExt, idtranscode_cmd) VALUES(:idfrom, :idto, :idtrans)");
	$stmt->bindValue(":idfrom", $fromExtID);
	$stmt->bindValue(":idto", $toExtID);
	$stmt->bindValue(":idtrans", $transcode_cmdID);
	$stmt->execute();
	
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
	try
	{
		$conn = getDBConnection();
		
		$stmt = $conn->prepare("SELECT idUser, username FROM User");
		$stmt->execute();
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		closeDBConnection($conn);
	}
	catch (PDOException $e)
	{
		appLog('Connection Failed: '.$e->getMessage(), appLog_INFO);
		if(isset($conn) && $conn && $conn->inTransaction())
		{
			$conn->rollBack();
		}		
		return false;
	}
	
	
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
	try
	{
		$conn = getDBConnection();
		
		$stmt = $conn->prepare("SELECT idUser, username, email, enabled, maxAudioBitrate, maxVideoBitrate, maxBandwidth
		FROM User WHERE idUser = :userid");
		$stmt->bindValue(":userid", $userid, PDO::PARAM_INT);
		$stmt->execute();
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $results[0];
	}
	catch (PDOException $e)
	{
		appLog('Connection Failed: '.$e->getMessage(), appLog_INFO);
		if(isset($conn) && $conn && $conn->inTransaction())
		{
			$conn->rollBack();
		}		
		return false;
	}
}

/**
* updates an existing user's settings
*/
function updateUser($userid, $json_settings){

	$userSettings = json_decode($json_settings,true);

	$av = new ArgValidator("handleArgValidationError");
	
	$av->validateArgs($userSettings, array(
		"username"				=> "string, notblank",
		"email"					=> "string",
		"enabled"				=> "int",
		"maxAudioBitrate"		=> "int",
		"maxVideoBitrate"		=> "int",
		"maxBandwidth"			=> "int",
	), true);
	
	$conn = null;
	try
	{
		$conn = getDBConnection();
		$conn->beginTransaction();
		
		$stmt = $conn->prepare("UPDATE User SET 
			username = :username,
			email = :email,
			enabled = :enabled,
			maxAudioBitrate = :maxAudioBitrate,
			maxVideoBitrate = :maxVideoBitrate,
			maxBandwidth = :maxBandwidth
			WHERE idUser = :idUser
			
		");
		
		$stmt->bindValue(":idUser", $userid, PDO::PARAM_INT);
		$stmt->bindValue(":username", $userSettings["username"], PDO::PARAM_STR);
		$stmt->bindValue(":email", $userSettings["email"], PDO::PARAM_STR);
		$stmt->bindValue(":enabled", $userSettings["enabled"], PDO::PARAM_INT);
		$stmt->bindValue(":maxAudioBitrate", $userSettings["maxAudioBitrate"], PDO::PARAM_INT);
		$stmt->bindValue(":maxVideoBitrate", $userSettings["maxVideoBitrate"], PDO::PARAM_INT);
		$stmt->bindValue(":maxBandwidth", $userSettings["maxBandwidth"], PDO::PARAM_INT);
		$stmt->execute();
		
		$conn->commit();
		
	}
	catch (PDOException $e)
	{
		appLog('Connection Failed: '.$e->getMessage(), appLog_INFO);
		if(isset($conn) && $conn && $conn->inTransaction())
		{
			$conn->rollBack();
		}		
		return false;
	}
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
		"username"				=> "string, notblank",
		"password"				=> "string, notblank",
		"email"					=> "string",
		"enabled"				=> "int",
		"maxAudioBitrate"		=> "int",
		"maxVideoBitrate"		=> "int",
		"maxBandwidth"			=> "int",
	), true);
	
	$conn = null;
	try
	{
		$conn = getDBConnection();
		$conn->beginTransaction();
		
		$stmt = $conn->prepare("INSERT INTO User(idRole,username, password, email, enabled, maxAudioBitrate, maxVideoBitrate, maxBandwidth) VALUES
			(
				:idRole,
				:username,
				:password,
				:email,
				:enabled,
				:maxAudioBitrate,
				:maxVideoBitrate,
				:maxBandwidth
			)
		");
		
		$stmt->bindValue(":idRole", 0, PDO::PARAM_INT); // hack in later
		$stmt->bindValue(":username", $userSettings["username"], PDO::PARAM_STR);
		$stmt->bindValue(":password", userLogin::hashPassword($userSettings["password"]), PDO::PARAM_STR);
		$stmt->bindValue(":email", $userSettings["email"], PDO::PARAM_STR);
		$stmt->bindValue(":enabled", $userSettings["enabled"], PDO::PARAM_INT);
		$stmt->bindValue(":maxAudioBitrate", $userSettings["maxAudioBitrate"], PDO::PARAM_INT);
		$stmt->bindValue(":maxVideoBitrate", $userSettings["maxVideoBitrate"], PDO::PARAM_INT);
		$stmt->bindValue(":maxBandwidth", $userSettings["maxBandwidth"], PDO::PARAM_INT);
		$stmt->execute();
		
		$conn->commit();
		
	}
	catch (PDOException $e)
	{
		appLog('Connection Failed: '.$e->getMessage(), appLog_INFO);
		if(isset($conn) && $conn && $conn->inTransaction())
		{
			$conn->rollBack();
		}		
		return false;
	}
	closeDBConnection($conn);
	
}

/**
* removes a user's account 
*/
function deleteUser($userid)
{
	$conn = null;
	try
	{
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
		
	}
	catch (PDOException $e)
	{
		appLog('Connection Failed: '.$e->getMessage(), appLog_INFO);
		if(isset($conn) && $conn && $conn->inTransaction())
		{
			$conn->rollBack();
		}		
		return false;
	}
	closeDBConnection($conn);
}

/**
* update a user's password
*/
function changeUserPassword($userid, $password)
{
	$conn = null;
	try
	{
		$conn = getDBConnection();
		$conn->beginTransaction();
		
		$stmt = $conn->prepare("UPDATE User SET Password = :password WHERE idUser = :idUser");
		
		$stmt->bindValue(":idUser", $userid, PDO::PARAM_INT);
		$stmt->bindValue(":password", userLogin::hashPassword($password), PDO::PARAM_STR);
	
		$stmt->execute();
		
		$conn->commit();
		
	}
	catch (PDOException $e)
	{
		appLog('Connection Failed: '.$e->getMessage(), appLog_INFO);
		if(isset($conn) && $conn && $conn->inTransaction())
		{
			$conn->rollBack();
		}		
		return false;
	}
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
	$conn = null;
	try
	{
		$conn = getDBConnection();		
		$stmt = $conn->prepare("SELECT path, displayName FROM mediaSource");	
		$stmt->execute();
		
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		return $results;
		
	}
	catch (PDOException $e)
	{
		appLog('Connection Failed: '.$e->getMessage(), appLog_INFO);		
		return false;
	}
	closeDBConnection($conn);
}

/**
* takes a JSON encoded string representing an object representing new media source settings to replace the old ones. 
*/
function saveMediaSourceSettings($settings_JSON)
{
	$settings = json_decode($settings_JSON,true);
	//validate them - may as well loop here and below - this doesn't get done much - it's not lile this file is MASSIVE
	foreach($settings as $mediaSource)
	{
		//validate them
		$av = new ArgValidator("handleArgValidationError");
		$mediaSource = $av->validateArgs($mediaSource,array(
			"path" 			=>	"string, notblank",
			"displayName" 	=> "string, notblank",
		),false);
	}
	
	$conn = null;
	try
	{
		$conn = getDBConnection();
		$conn->beginTransaction();
		
		//remove old media source settings 
		$stmt = $conn->prepare("DELETE FROM mediaSource");
		$stmt->execute();
		//insert new settings
		foreach($settings as $mediaSource)
		{			
			var_dump_pre($mediaSource);
			$stmt = $conn->prepare("INSERT INTO mediaSource(path, displayName) VALUES (:path, :dname)");		
			$stmt->bindValue(":path", $mediaSource["path"], PDO::PARAM_STR); 
			$stmt->bindValue(":dname", $mediaSource["displayName"], PDO::PARAM_STR); 
			$stmt->execute();
		}
		$conn->commit();
		
	}
	catch (PDOException $e)
	{
		appLog('Connection Failed: '.$e->getMessage(), appLog_INFO);
		if(isset($conn) && $conn && $conn->inTransaction())
		{
			$conn->rollBack();
		}		
		return false;
	}
	closeDBConnection($conn);
}






?>