<?php
require_once("constants.php");

$config = array();

//db 
define("DBPATH", "db/nick-dev.db");
define("PDO_DSN", "sqlite:".DBPATH);
define("DB_VERSION", 101); // indicates what version of the db the code uses - to be matched against the one in the db

//api version
define("APIVERSION","0.58");

define("APPNAME", "Ultrasonic");

//file to log messages to
$config["logFile"] = "/tmp/ultrasonic.log";

//logging
$config["logLevel"] = appLog_DEBUG2;

/*$config["supportedPlayFormatExts"] = array(
	"flv",
);*/

//user password salt
$config["passwordSalt"] = "JbC^*(I4GJbgz7V)";

//sesssion data
$config["sessionName"] = "Ultrasonic";



?>