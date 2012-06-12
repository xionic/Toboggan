<?php
/**
* entry point for rest api
* TODO: add server side logging calls
*/

require_once("include/functions.php");
require_once("classes/REST_Helpers.class.php");
require_once("classes/userLogin.class.php");

try
{

	//check that the db schema version that the code uses is the same as the actual db
	if(!validateDBVersion())
	{
		reportServerError("Server schema version mismatch. This is a server problem.");
	}

	//argument validator
	$av = new ArgValidator("handleArgValidationError");

	//check API version and key
	$apiargs = $av->validateArgs($_GET, array(
		"apikey" => array("string", "notblank"),
		"apiver" => array("numeric"),
	));
	//api version
	if($apiargs["apiver"] != APIVERSION)
	{//unsupported api version
		reportError("Invalid API Version. This server uses version ". APIVERSION, 412, "text/plain");
	}
	//apikey
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
		if(userLogin::checkLoggedIn() === false)
		{
			reportError("Authentication failed", 401, "text/plain");
			exit();
		}
	}
	if(isset($_GET) && isset($_GET["action"]))
		$action = $_GET["action"];
	else
		$action = "";
	appLog("Received request for action ". $action, appLog_DEBUG);

	//main selector between actions
	switch($action)
	{
		case "listMediaSources":		
			outputMediaSourcesList_JSON();
			break;
			
		case "listDirContents":
			//validate args		
			$args = $av->validateArgs($_GET, array(
				"dir" => array("string"),
				"mediaSourceID"	=>	array("int", "notzero"),
			));
			
				
			outputDirContents_JSON($args["dir"], $args["mediaSourceID"]);
			break;
			
		case "downloadFile": //download a file unmodified
			checkActionAllowed("downloadFile");	
			$_GET["streamerID"] = 0; //hack through the switch and allow to follow through the getStream handler
			
		case "getStream": // INPUT VALIDITY CHECKING SHOULD BE BETTER HERE
			checkActionAllowed("streamFile");	
			//validate arguments
			$args = $av->validateArgs($_GET, array(
				"dir" 				=> array("string"),
				"mediaSourceID"		=> array("int", "notzero"),
				"filename"			=> array("string", "notblank"),
				"streamerID" 		=> array("int")
			));
					
			//get full path to file
			$mediaSourcePath = getMediaSourcePath($args["mediaSourceID"]);
			if(!$mediaSourcePath)
			{
				reportError("Invalid/Non-Existant media source");
				die;
			}
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
				"settingsBlob" 	=> array("string"),
				"apikey"		=> array("string", "notblank")
			));
			//save the settings
			saveClientSettings($args["settingsBlob"], $args["apikey"], userLogin::getCurrentUserID());
			
			break;
			
		case "retrieveClientSettings":
			$args = $av->validateArgs($_GET, array(
				"apikey"		=> array("string", "notblank")
			));

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
		
		case "search":
			$args = $av->validateArgs($_GET, array(
				"mediaSourceID"		=> array("string", "notblank"), //string to allow for 'all'
				"dir"				=> array("string"),
				"query"				=> array("string", "notblank"),
			));
			
			outputSearchResults_JSON($args["mediaSourceID"], $args["dir"], $args["query"]);
			break;
			
		case "retrieveStreamerSettings":
			checkActionAllowed("administrator");
			outputStreamerSettings_JSON();
			break;
			
		case "saveStreamerSettings":
			checkActionAllowed("administrator");
			$args = $av->validateArgs($_POST, array(			
				"settings"		=> array("string", "notblank"),
			));
			
			saveStreamerSettings($args["settings"]);
			break;
			
		case "listUsers":
			checkActionAllowed("administrator");
			outputUserList_JSON();
			break;
			
		case "retrieveUserSettings":
			checkActionAllowed("administrator");
			$args = $av->validateArgs($_GET, array(			
				"userid"	=> array("int", "notblank"),
			));
			outputUserSettings_JSON($args["userid"]);
		break;
		
		case "updateUserSettings":
			checkActionAllowed("administrator");
			$argsPOST = $av->validateArgs($_POST, array(			
				"settings"	=> array("string", "notblank"),
			)); 
			$argsGET = $av->validateArgs($_GET, array(			
				"userid"	=> array("int","notblank"),
			));
			
			updateUser($argsGET["userid"], $argsPOST["settings"]);
		break;
		
		case "addUser":
			checkActionAllowed("administrator");
			$args = $av->validateArgs($_POST, array(			
				"settings"	=> array("string", "notblank"),
			));
			addUser($args["settings"]);
		break;
		
		case "deleteUser";
			checkActionAllowed("administrator");
			$args = $av->validateArgs($_GET, array(			
				"userid"	=> array("int", "notblank"),
			));
			deleteUser($args["userid"]);
		break;
		
		case "changeUserPassword":
			checkActionAllowed("administrator");
			$argsGET = $av->validateArgs($_GET, array(
				"userid"	=> array("int", "notblank", "optional"),
			));
			$argsPOST = $av->validateArgs($_POST, array(			
				"password"	=> array("string", "notblank"),
			));
			
			if(!isset($argsGET["userid"])) // it's optional - if not set use current userid
				$userid = userLogin::getCurrentUserID();
			else
				$userid = $argsGET["userid"];
			changeUserPassword($userid, $argsPOST["password"]);
		break;
		
		case "getUserTrafficStats":
			outputUserTrafficLimitStats_JSON(userLogin::getCurrentUserID());
		break;
		
		case "retrieveMediaSourceSettings":
			checkActionAllowed("administrator");
			outputMediaSourceSettings_JSON();
			break;
		
		case "saveMediaSourceSettings":
			checkActionAllowed("administrator");
			$argsPOST = $av->validateArgs($_POST, array(			
				"mediaSourceSettings"	=> array("string", "notblank"),
			));
			saveMediaSourceSettings($argsPOST["mediaSourceSettings"]);
			break;	
		
		case "":
			restTools::sendResponse("No action specified", 400, "text/plain");
			break;
			
		default:
			restTools::sendResponse("Action not supported", 400, "text/plain");
		}
	}
catch(PDOException $pdoe)
{
	/*var_dump_pre($pdoe);
	if(isset($conn) && $conn && $conn->inTransaction())
	{
		appLog("Rolling back DB transaction", appLog_ERROR);
		$conn->rollBack();
	}*/
	appLog(var_export($pdoe,true), appLog_DEBUG2);
	reportServerError('PDOException: '.$pdoe->getMessage(),500);
}


?>
