<?php
require_once("constants.php");

$config = array();

//db 
define("DBPATH", "db/nick-dev.db");
define("PDO_DSN", "sqlite:".DBPATH);

//api version
define("APIVERSION","0.57");

//file to log messages to
$config["logFile"] = "/tmp/ultrasonic.log";

//logging
$config["logLevel"] = appLog_DEBUG2;

$config["supportedPlayFormatExts"] = array(
	"flv",
);

//user password salt
$config["passwordSalt"] = "JbC^*(I4GJbgz7V)";

//sesssion data
$config["sessionName"] = "Ultrasonic";



?>