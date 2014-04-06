<?php


/**
* get a database connection
*/
function getDBConnection()
{
	if(!is_readable(DBPATH)){
		reportError("DB does not exist or is not readable. If this is a new installation did you run install.sh? (See README)", 500); 
	}	
	$db = new PDO(PDO_DSN);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	//enable foreign key enforcement - not enabled by default in sqlite and must be done per connection
	$db->exec("PRAGMA foreign_keys = ON");	
	
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
		//see if the mediaSourceID is already in the db
		$stmt = $conn->prepare("SELECT 1 FROM mediaSource WHERE idmediaSource = :idmediaSource");
		$stmt->bindValue(":idmediaSource",$mediaSource["mediaSourceID"], PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetchAll(PDO::FETCH_ASSOC);	
		
		if(!$row)
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
	
	//clear up permissions too
	$query = "DELETE FROM UserPermission WHERE idAction = " . PERMISSION_ACCESSMEDIASOURCE . " AND targetObjectID NOT IN (" . implode($tempArr,","). ");";
	
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
* function to retrieve the durationCmd from the db for a fromExt
*/
function getDurationCommand($fromExt)
{
	$conn = getDBConnection();
	$stmt = $conn->prepare("
		SELECT 
			command			
		FROM 
			FileType 
			INNER JOIN Command ON (idcommand = iddurationCmd)
		WHERE Extension = :fromExt AND command IS NOT NULL");
	$stmt->bindValue(":fromExt",$fromExt, PDO::PARAM_STR);
	$stmt->execute();

	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	closeDBConnection($conn);	
	
	//ensure the user has permission to user the streamer that the duration command is defined in
	if(count($results))
	{		
		return $results[0]["command"];
	}
	return null;
}


?>
