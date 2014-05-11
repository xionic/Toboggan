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
* returns a schema
*/
function getSchema($name){
	global $schema;
	return $schema[$name];}


/**
* Outputs metadata about a file
*/
function outputFileMetaData_JSON($mediaSourceID, $dir, $filename)
{	
	$fileMetaData = getFileMetaData($mediaSourceID, $dir, $filename);
	restTools::sendResponse(json_encode($fileMetaData), 200, JSON_MIME_TYPE);
}

function getFileMetaData($mediaSourceID, $dir, $filename)
{	
	//standard file object info
	$mediaSourcePath = getMediaSourcePath($mediaSourceID);
	$filePath = $mediaSourcePath . "/" . normalisePath($dir . "/" . $filename);

	if(!file_exists($filePath))
	{
		appLog("Request for non-existant file: ".$filePath, appLog_INFO);
		reportError("Requested file does not exist");
		exit();
	}
	
	//filesize
	$fileMetaData = getFileObject($filePath);
	$fileMetaData["filesize"] = filesize($filePath);
	
	//duration
	if(($duration = getMediaDuration($filePath)) !== null)
		$fileMetaData["duration"] = getMediaDuration($filePath);
	
	//tags
	$fileMetaData["tags"] = getFileTags($filePath);
	
	
	return $fileMetaData;
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

/**
* Handle session starting and setup
*/
function start_session()
{
	//start session
	session_name(getConfig("sessionName"));
	session_start();
}

?>