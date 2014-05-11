<?php
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
* outputs a JSON object of a all the userid and usernames
*/
function outputUserList_JSON()
{
	$users = getUsers();
	restTools::sendResponse(json_encode($users), 200, JSON_MIME_TYPE);
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
	restTools::sendResponse(json_encode($user), 200, JSON_MIME_TYPE);
}

/**
* outputs a json representation of a user
*/
function outputUserMetaData_JSON($userid)
{
	$user = getUserObject($userid);
	$outputObj = array(
		"idUser" 		=> $user["idUser"], 
		"username" 		=> $user["username"],
		"permissions" 	=> $user["permissions"]["general"],
	);
	restTools::sendResponse(json_encode($outputObj), 200);
}

/**
* returns an array representing a user
*/
function getUserObject($userid)
{
	if(!is_numeric($userid))
	{
		throw new Exception("\$userid passed to getUserObject() must be numeric");
	}
	
	$conn = getDBConnection();
	$conn->beginTransaction();
	
	//assemble user info
	$stmt = $conn->prepare("
		SELECT idUser, username, email, 
			CASE WHEN enabled = 1 THEN 'Y' ELSE 'N' END as enabled, 
			maxAudioBitrate, maxVideoBitrate, maxBandwidth,				
			CASE WHEN enableTrafficLimit = 1 THEN 'Y' ELSE 'N' END as enableTrafficLimit,			
			trafficLimit, trafficLimitPeriod,
			trafficUsed,
			(trafficLimitPeriod - (strftime('%s','now') - (trafficLimitStartTime))) as timeToReset	
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
				AND targetObjectID IS NULL
				AND NOT Action.idAction = :accessStreamerAction
				AND NOT Action.idAction = :accessMediaSourceAction
		;
	");
	$stmt->bindValue(":userid", $userid, PDO::PARAM_INT);
	$stmt->bindValue(":userid", $userid, PDO::PARAM_INT);
	$stmt->bindValue(":accessStreamerAction", PERMISSION_ACCESSSTREAMER, PDO::PARAM_INT);
	$stmt->bindValue(":accessMediaSourceAction", PERMISSION_ACCESSMEDIASOURCE, PDO::PARAM_INT);
	$stmt->execute();
	$userStandardPerms = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$userObj["permissions"]["general"] = $userStandardPerms;
	
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
		SELECT 
			idfileConverter as id, 
			(ftfrom.Extension || ' -> '  || ftto.extension) as displayName, 
			CASE WHEN PermissionAction.idAction IS NOT NULL THEN 'Y' ELSE 'N' END as granted
		FROM FileConverter
				CROSS JOIN User 
				LEFT JOIN (
					SELECT * FROM UserPermission 
							INNER JOIN Action USING(idAction) 
						WHERE Action.actionName='accessStreamer'
				) AS PermissionAction 
					ON (
						PermissionAction.targetObjectID = FileConverter.idfileConverter 
						AND PermissionAction.idUser=User.idUser
					) 
				INNER JOIN FileType ftfrom on (FileConverter.fromidfileType = ftfrom.idfileType)
				INNER JOIN FileType ftto on (FileConverter.toidfileType = ftto.idfileType)
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

	$av->validateArgs(array("userid" => $userid),array(
		"userid"	=> array("int", "lbound 0"),
	));
	
	$av->validateArgs($userSettings, array(
		"username"				=> array("string", "notblank"),
		"email"					=> array("string"),
		"enabled"				=> array("string", "regex /[YN]/"),
		"maxAudioBitrate"		=> array("int", "lbound 0"),
		"maxVideoBitrate"		=> array("int", "lbound 0"),
		"maxBandwidth"			=> array("int", "lbound 0"),
		"enableTrafficLimit"	=> array("string", "regex /[YN]/"),	
		"permissions"			=> array("array"),
	));
	
	//validate conditionally
	if($userSettings["enableTrafficLimit"] === 1)
	{
		$av->validateArgs($userSettings, array(
			"trafficLimit"			=> array("int", "lbound 1"),	
			"trafficLimitPeriod"	=> array("int", "lbound 1"),	
		));
	}
	
	$av->validateArgs($userSettings["permissions"], array(
		"general"				=> array("array"),
		"mediaSources"			=> array("array"),
		"streamers"				=> array("array"),
	));
	
	foreach($userSettings["permissions"]["general"] as $perm)
	{
		$av->validateArgs($perm, array(
			"id"				=> array("int"),
			"granted"			=> array("string", "notblank", "regex /[YN]/"),
		));
	}
	foreach($userSettings["permissions"]["mediaSources"] as $perm)
	{
		$av->validateArgs($perm, array(
			"id"				=> array("int"),
			"granted"			=> array("string", "notblank", "regex /[YN]/"),
		));
	}
	foreach($userSettings["permissions"]["streamers"] as $perm)
	{
		$av->validateArgs($perm, array(
			"id"				=> array("int"),
			"granted"				=> array("string", "notblank", "regex /[YN]/"),
		));
	}
	
	//ensure that the username does not already exist
	//get user's existing username
	$currUserObj = getUserInfoFromID($userid);
	
	if($currUserObj["username"] != $userSettings["username"] && getUserInfo($userSettings["username"]) !== null) // if the username is being changed and the new one already exists
	{
		reportError("Username already exists", 400);
		exit();
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
	$stmt->bindValue(":enabled", $userSettings["enabled"]==='Y', PDO::PARAM_BOOL);
	$stmt->bindValue(":maxAudioBitrate", $userSettings["maxAudioBitrate"], PDO::PARAM_INT);
	$stmt->bindValue(":maxVideoBitrate", $userSettings["maxVideoBitrate"], PDO::PARAM_INT);
	$stmt->bindValue(":maxBandwidth", $userSettings["maxBandwidth"], PDO::PARAM_INT);
	$stmt->bindValue(":enableTrafficLimit", $userSettings["enableTrafficLimit"] === 'Y', PDO::PARAM_BOOL );
	$stmt->bindValue(":trafficLimit", $userSettings["trafficLimit"], PDO::PARAM_INT);
	$stmt->bindValue(":trafficLimitPeriod", $userSettings["trafficLimitPeriod"], PDO::PARAM_INT);
	$stmt->execute();
		
	//update user permissions	
	//build a normalised permissions structure to match the db structure (i.e. merge in the special cases [mediaSources and streamers])
	$newUserPermissions = array();
	//action permissions
	foreach($userSettings["permissions"]["general"] as $perm)
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
		"enabled"				=> array("string", "regex /[YN]/"),
		"maxAudioBitrate"		=> array("int"),
		"maxVideoBitrate"		=> array("int"),
		"maxBandwidth"			=> array("int"),
		"enableTrafficLimit"	=> array("string", "regex /[YN]/"),
		"permissions"			=> array("array"),
	));
	//validate conditionally
	if($userSettings["enableTrafficLimit"] === 1)
	{
		$av->validateArgs($userSettings, array(
			"trafficLimit"			=> array("int", "lbound 1"),	
			"trafficLimitPeriod"	=> array("int", "lbound 1"),	
		));
	}
	
	//validate permissions
	$av->validateArgs($userSettings["permissions"], array(
		"general"				=> array("array"),
		"mediaSources"			=> array("array"),
		"streamers"				=> array("array"),
	));
	foreach($userSettings["permissions"]["general"] as $perm)
	{
		$av->validateArgs($perm, array(
			"id"				=> array("int"),
			"granted"			=> array("string", "notblank", "regex /[YN]/"),
		));
	}
	foreach($userSettings["permissions"]["mediaSources"] as $perm)
	{
		$av->validateArgs($perm, array(
			"id"				=> array("int"),
			"granted"			=> array("string", "notblank", "regex /[YN]/"),
		));
	}
	foreach($userSettings["permissions"]["streamers"] as $perm)
	{
		$av->validateArgs($perm, array(
			"id"				=> array("int"),
			"granted"				=> array("string", "notblank", "regex /[YN]/"),
		));
	}
	
	//ensure that the username does not already exist
	if(getUserInfo($userSettings["username"]) !== null)
	{
		reportError("Username already exists", 400);
		exit();
	}
	
	$conn = null;
	
	$conn = getDBConnection();
	$conn->beginTransaction();
	
	$stmt = $conn->prepare("
	INSERT INTO User
		(
			username,
			password,
			email,
			enabled, 
			maxAudioBitrate,
			maxVideoBitrate,
			maxBandwidth,
			enableTrafficLimit,
			trafficLimit,
			trafficLimitPeriod
		) VALUES
		(
			:username,
			:password,
			:email,
			:enabled,
			:maxAudioBitrate,
			:maxVideoBitrate,
			:maxBandwidth,
			:enableTrafficLimit,
			:trafficLimit,
			:trafficLimitPeriod
		)
	");
	
	$stmt->bindValue(":username", $userSettings["username"], PDO::PARAM_STR);
	$stmt->bindValue(":password", userLogin::hashPassword($userSettings["password"]), PDO::PARAM_STR);
	$stmt->bindValue(":email", $userSettings["email"], PDO::PARAM_STR);
	$stmt->bindValue(":enabled", $userSettings["enabled"], PDO::PARAM_INT);
	$stmt->bindValue(":maxAudioBitrate", $userSettings["maxAudioBitrate"], PDO::PARAM_INT);
	$stmt->bindValue(":maxVideoBitrate", $userSettings["maxVideoBitrate"], PDO::PARAM_INT);
	$stmt->bindValue(":maxBandwidth", $userSettings["maxBandwidth"], PDO::PARAM_INT);
	$stmt->bindValue(":enableTrafficLimit", $userSettings["enableTrafficLimit"], PDO::PARAM_INT);
	$stmt->bindValue(":trafficLimit", $userSettings["trafficLimit"], PDO::PARAM_INT);
	$stmt->bindValue(":trafficLimitPeriod", $userSettings["trafficLimitPeriod"], PDO::PARAM_INT);
	$stmt->execute();
	
	//insert user permissions		
	$userid = $conn->lastInsertId();
	
	//build a normalised permissions structure to match the db structure (i.e. merge in the special cases [mediaSources and streamers])
	$newUserPermissions = array();
	//action permissions
	foreach($userSettings["permissions"]["general"] as $perm)
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
* removes a user's account 
*/
function deleteUser($userid)
{
	if( userLogin::getCurrentUserID() == ((int)$userid)) //user cannot delete themselves
	{
		reportError("Cannot delete current user", 403);
		exit();
	}

	$conn = null;
	
	$conn = getDBConnection();
	$conn->beginTransaction();
	
	//remove client settings for the user 
	$stmt = $conn->prepare("DELETE FROM ClientSettings WHERE idUser = :idUser");		
	$stmt->bindValue(":idUser", $userid, PDO::PARAM_INT); 
	$stmt->execute();
	
	//remove the user's permissions 
	$stmt = $conn->prepare("DELETE FROM UserPermission WHERE idUser = :idUser");		
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
	if($password == "47DEQpj8HBSa+/TImW+5JCeuQeRkm5NMpJWZG3hSuFU=") //blank sha1 hash
	{
		reportError("Password cannot be blank", 400);
		exit();
	}
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
* checks whether the currently auth'd user has permission to perform the action given by actionName
*/
function checkUserPermission($actionName, $targetObjectID = false)
{
	$userID = userLogin::getCurrentUserID();
	
	appLog(
		"Checking userid '" . $userID . "' has permission for action '" . 
		$actionName . "' with targetObjectID '" . $targetObjectID . "'", appLog_DEBUG
	);
	
	//cache permissions per session if set
	if(getConfigItem("cache_permissions"))
	{	//appLog(var_export($GLOBALS,true));
		// build cache of permissions for speed 	
		if(!isset($GLOBALS["cache_permissions"]))
		{
			$sql = "SELECT actionName, targetObjectID 
					FROM UserPermission 
					INNER JOIN Action USING(idAction)
					WHERE 
						`UserPermission`.idUser = :idUser
					";		
			
			$conn = getDBConnection();	
			$conn->beginTransaction();
			$stmt = $conn->prepare($sql);	
			
			$stmt->bindValue("idUser", $userID, PDO::PARAM_INT);
						
			$stmt->execute();
			
			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
			$GLOBALS["cache_permissions"] = $results;
			
			$conn->commit();
			closeDBConnection($conn);
		}
		
		//use the cache
		foreach($GLOBALS["cache_permissions"] as $permission)
		{	
			if($permission["actionName"] == $actionName)
			{
				//if the permission relates to a specific object it must match
				if($targetObjectID === false || $permission["targetObjectID"] == $targetObjectID)
				{
					return true;
				}
			}
			
		}	
		appLog(
			"Permission denied to user with userid '" . userLogin::getCurrentUserID() . "' for action '" . 
			$actionName . "' with targetObjectID '" . $targetObjectID . "'", appLog_DEBUG
		);
		return false;
	
	}
	else //lookup from DB - do not build or use cache
	{
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
		SELECT CASE WHEN enableTrafficLimit = 1 THEN 'Y' ELSE 'N' END as enableTrafficLimit, trafficLimit, trafficUsed, trafficLimitPeriod, (trafficLimitPeriod - (strftime('%s','now') - (trafficLimitStartTime))) as timeToReset 
			FROM User 
			WHERE idUser = :userid");	
	
	$stmt->bindValue("userid", $userid, PDO::PARAM_INT);
		
	$stmt->execute();

	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);	

	$conn->commit();
	closeDBConnection($conn);
	
	
	//check that the user actually has a limit
	if(count($results) == 0  || $results[0]["enableTrafficLimit"] == 'N')
	{
		$returnVal["enableTrafficLimit"] = 'N';
	}
	else
	{
		$returnVal = $results[0];
	}
	
	return $returnVal;
}

?>
