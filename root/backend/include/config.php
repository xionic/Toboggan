<?php

$config = array();

//to be replaced by config crom the db
require_once("config.fromDB.php");

//db 
define("DBPATH", "/var/wwws/projects/ultrasonic/root/backend/db/nick-dev.db");
define("PDO_DSN", "sqlite:".DBPATH);

//logging
$config["logLevel"] = appLog_VERBOSE;

$config["supportedPlayFormatExts"] = array(
	"flv",
);


?>