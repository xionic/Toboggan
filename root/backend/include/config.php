<?php
require_once("constants.php");

$config = array();

/**********************************
* USER CONFIG
***********************************/

//file to log messages to
$config["logFile"] = "/tmp/toboggan.log";

//logging
$config["logLevel"] = appLog_DEBUG;

//cache permissions per session
$config["cache_permissions"] = true; // this option might be removed in future

/**********************************
*DO NOT EDIT BELOW HERE
***********************************/

//db 
define("DBPATH", "db/main.db");
define("PDO_DSN", "sqlite:".DBPATH);
define("DB_VERSION", 104); // indicates what version of the db the code uses - to be matched against the one in the db

//api version
define("APIVERSION","0.6");

//app settings
define("APPNAME", "Toboggan");

//user password salt
$config["passwordSalt"] = "JbC^*(I4GJbgz7V)";

//session data
$config["sessionName"] = "Toboggan";

//max amount of data to read in one fread call
$config["singleReadMaxBytes"] = 1048576; // 1MB



?>
