<?php
/**
* entry point for rest api
* TODO: add server side logging calls
*/

require_once("include/functions.php");
require_once("classes/REST_Helpers.class.php");

$action = @$_GET["action"];

switch($action)
{
	case "listMediaSources":
		restTools::sendResponse(getMediaSourceID_JSON(),200);
		break;
		
	case "listDirContents":
		if(!@$_GET["dir"])
		{
			$dir = "";
		}
		else
		{
			$dir = urldecode($_GET["dir"]);
		}
			
		getDirContents_JSON(urldecode($dir, $_GET["mediaSourceID"]));
		break;
		
	case "getStream": // INPUT VALIDITY CHECKING SHOULD BE BETTER HERE
		$partialfilepath	= @$_GET["dir"];
		$filename			= @$_GET["filename"];
		$mediaSourceID		= @$_GET["mediaSourceID"];
		$streamerID			= @$_GET["streamerID"];
		
		if(!$partialfilepath || !$mediaSourceID || !$streamerID || !$filename)
		{ // invalid data passed
			restTools::sendResponse("Invalid argument", 400, "text/plain");
		}
		//get full path to file
		$fullfilepath = getMediaSourcePath($mediaSourceID).normalisePath($partialfilepath.$filename);
		
		//output the media stream via a streamer
		outputStream($fullfilepath, $streamerID);
		break;
		
	case "":
		restTools::sendResponse("No action specified", 400, "test/plain");
	default:
		restTools::sendResponse("Action not supported", 400, "test/plain");
		
}

?>
