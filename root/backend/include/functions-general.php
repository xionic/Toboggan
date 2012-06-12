<?php

require_once("classes/userLogin.class.php");

/**
* get a config option
*/
function getConfig($name){
	global $config;
	return $config[$name];
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
		$streamers[] = array("extension" => $s->toExt, "streamerID" => $s->id, "mediaType" => $s->outputMediaType);
	}
	return array(
		"filename" 		=> $filename,
		"displayName"	=> $displayName,
		"streamers"		=> $streamers,
		
	);
}
/**
* Outputs metadata about a file
*/
function outputFileMetaData_JSON($mediaSourceID, $dir, $filename)
{
	$mediaSourcePath = getMediaSourcePath($mediaSourceID);
	$filePath = $mediaSourcePath . "/" . normalisePath($dir . "/" . $filename);
	
	$fileMetaData = getFileObject($filePath);
	restTools::sendResponse(json_encode($fileMetaData), 200, "text/json");
}

/**
* checks that the current user is allowed to perform the action given by action name - if not report error and stop.
*/
function checkActionAllowed($actionName, $targetObjectID = false)
{
	if(!checkUserPermission($actionName, $targetObjectID))
	{
		appLog(
			"Permission denied for action '" . $actionName . "' for user with ID '" . 
			userLogin::getCurrentUserID() . "' and targetObjectID '" . $targetObjectID . "'"
			,appLog_INFO
		);
		reportError("Permission Denied", 403);
	}
}
?>