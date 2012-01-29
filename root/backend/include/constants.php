<?php

//bitrate magic number
define("NO_MAX_BITRATE", -1);

//log levels
define("appLog_DEBUG", 5);
define("appLog_VERBOSE", 4);
define("appLog_INFO",3);

//db 
define("DBPATH", "/var/wwws/projects/ultrasonic/root/backend/db/nick-dev.db");
define("PDO_DSN", "sqlite:".DBPATH);
?>