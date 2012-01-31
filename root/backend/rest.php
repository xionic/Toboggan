<?php
/**
* entry point for rest api
* TODO: add server side logging calls
*/

require_once("include/functions.php");
require_once("classes/REST_Helpers.class.php");

$action = @$_GET["action"];
appLog("Received request for action ". $action, appLog_DEBUG);

switch($action)
{
	case "listMediaSources":
		restTools::sendResponse(getMediaSourceID_JSON(),200);
		break;
		
	case "listDirContents":
		if(empty($_GET["dir"]))
		{
			$dir = "";
		}
		else
		{
			$dir = urldecode($_GET["dir"]);
		}
			
		getDirContents_JSON(urldecode($dir), $_GET["mediaSourceID"]);
		break;
		
	case "downloadFile": //download a file unmodified
		$_GET["streamerID"] = 0; //hack through the switch and allow to follow through the getStream handler
		
	case "getStream": // INPUT VALIDITY CHECKING SHOULD BE BETTER HERE
		$partialfilepath	= @$_GET["dir"]; // can be empty
		$filename			= @$_GET["filename"];
		$mediaSourceID		= @$_GET["mediaSourceID"];
		$streamerID			= @$_GET["streamerID"];
		
		// check inputs validity		
		if(!$mediaSourceID || ((int)$mediaSourceID) == 0)
			restTools::sendResponse("mediaSourceID is invalid", 400, "text/plain");
		elseif(!is_numeric($streamerID))
			restTools::sendResponse("streamerID is invalid", 400, "text/plain");
		elseif(!$filename)
			restTools::sendResponse("filename is invalid", 400, "text/plain");
		
		//get full path to file
		$fullfilepath = getMediaSourcePath($mediaSourceID).normalisePath($partialfilepath.$filename);
		
		//output the media stream via a streamer
		outputStream($streamerID, $fullfilepath, 200);
		break;
		
	case "":
		restTools::sendResponse("No action specified", 400, "test/plain");
		break;
		
	default:
		restTools::sendResponse("Action not supported", 400, "test/plain");
		
}

?>
