<?php

require_once("functions.php");

$streamerID = (int) $_GET["profile"];

//get a valid path for the file
$file = getValidPath(urldecode($_GET["file"]));

//generate and output the media stream
outputStream($file, $streamerID);




?>