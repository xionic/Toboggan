<?php
/**
* entry point for rest api
* TODO: add server side logging calls
*/

require_once("include/functions.php");
require_once("classes/REST_Helpers.class.php");
require_once("classes/userLogin.class.php");

//argument validator
$av = new ArgValidator("handleArgValidationError");

//check API key
$apiargs = $av->validateArgs($_GET, array(
	"apikey" => "string, notblank",
), true);
if(!checkAPIKey($apiargs["apikey"]))
{ // invalid api key
	reportError("Invalid API Key", 412, "text/plain");
}

//start session
session_name(getConfig("sessionName"));
session_start();

//check user is auth'd
if(isset($_GET["action"]) && $_GET["action"] != "login") // special case
{
	//echo "'".(userLogin::checkLoggedIn())."'\n";
	if(userLogin::checkLoggedIn() === false)
	{
		reportError("Authentication failed", 401, "text/plain");
		exit();
	}
}

$action = @$_GET["action"];
appLog("Received request for action ". $action, appLog_DEBUG);




switch($action)
{
	case "listMediaSources":		
		restTools::sendResponse(getMediaSourceID_JSON(),200);
		break;
		
	case "listDirContents":
		//validate args		
		$args = $av->validateArgs($_GET, array(
			"dir" => "string",
			"mediaSourceID"	=>	"int, notzero",
		), true);
		
			
		getDirContents_JSON($args["dir"], $args["mediaSourceID"]);
		break;
		
	case "downloadFile": //download a file unmodified
		$_GET["streamerID"] = 0; //hack through the switch and allow to follow through the getStream handler
		
	case "getStream": // INPUT VALIDITY CHECKING SHOULD BE BETTER HERE
		
		//validate arguments
		$args = $av->validateArgs($_GET, array(
			"dir" 				=> "string",
			"mediaSourceID"		=> "int, notzero",
			"filename"			=> "string, notblank",
			"streamerID" 		=> "int"
		), true);
				
		//get full path to file
		$fullfilepath = getMediaSourcePath($args["mediaSourceID"]).normalisePath($args["dir"].$args["filename"]);
		
		//output the media stream via a streamer
		if(!outputStream($args["streamerID"], $fullfilepath))
		{
			return; //error outputting stream - error should have been reported by outputStream()
		}
		break;
		
	case "login":	
		if(!userLogin::validate())
		{
			reportError("Login failed", 401, "text/plain");
			exit();
		}
		restTools::sendResponse("Login successful", 200, "text/plain");
		break;
		
	case "logout":
		userLogin::logout();
		
		break;
	case "saveClientSettings": 
		//args validation
		$args = $av->validateArgs($_GET, array(
			"settingsBlob" => "string",
			"apikey"		=> "string, notblank"
		),true);
		//save the settings
		saveClientSettings($args["settingsBlob"], $args["apikey"], userLogin::getCurrentUserID());
		
		break;
		
	case "retrieveClientSettings":
		$args = $av->validateArgs($_GET, array(
			"apikey"		=> "string, notblank"
		),true);

		$clientSettings = getClientSettings($args["apikey"], userLogin::getCurrentUserID());
		if(!$clientSettings) // no settings to retrieve
		{
			appLog("No Client settings saved for apikey:'".$args['apikey']."' and userid:".userLogin::getCurrentUserID(), appLog_DEBUG);
			restTools::sendResponse("No client settings to return", 204, "text/plain");
		}
		else
		{
			appLog("Returning Client settings for apikey:'".$args['apikey']."' and userid:".userLogin::getCurrentUserID(), appLog_DEBUG);
			restTools::sendResponse($clientSettings,200, "text/json");
		}
		break;
		
	case "":
		restTools::sendResponse("No action specified", 400, "text/plain");
		break;
		
	default:
		restTools::sendResponse("Action not supported", 400, "text/plain");
		
}

?>
