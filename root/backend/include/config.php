<?php
require_once("constants.php");

$config = array();

/**********************************
* USER CONFIG
***********************************/

//file to log messages to
$config["logFile"] = "/tmp/ultrasonic.log";

//logging
$config["logLevel"] = appLog_DEBUG2;

//cache permissions per session
$config["cache_permissions"] = true;

/**********************************
*DO NOT EDIT BELOW HERE
***********************************/

//db 
define("DBPATH", "db/nick-dev.db");
define("PDO_DSN", "sqlite:".DBPATH);
define("DB_VERSION", 102); // indicates what version of the db the code uses - to be matched against the one in the db

//api version
define("APIVERSION","0.58");

//app settings
define("APPNAME", "Ultrasonic");

//user password salt
$config["passwordSalt"] = "JbC^*(I4GJbgz7V)";

//sesssion data
$config["sessionName"] = "Ultrasonic";



?>