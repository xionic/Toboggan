<?php
require_once("constants.php");

$config = array();

//to be replaced by config crom the db
require_once("config.fromDB.php");

//db 
define("DBPATH", "db/nick-dev.db");
define("PDO_DSN", "sqlite:".DBPATH);

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