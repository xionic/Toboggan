<?php
/**
* entry point for rest api
*/

require_once("include/functions.php");
require_once("classes/REST_Helpers.php");

$action = @$_GET["action"];

switch($action){
	case "listMediaSources":
		getMediaSourceID_JSON();
		break;
	case "listDirContents":
		if(!@$_GET["dir"])
			$_GET["dir"] = "";
		getDirContents_JSON(urldecode($_GET["dir"]), $_GET["mediaSourceID"]);
		break;
	case "getStream":
	
		break;
	case "":
		restTools::sendResponse("No action specified", 400, "test/plain");
	default:
		restTools::sendResponse("Action not supported", 400, "test/plain");
		
}

?>
