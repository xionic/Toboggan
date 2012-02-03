<?php
	include("include/functions.php");
	session_name(getConfig("sessionName"));
	session_start();
	$_SESSION["userid"] = 1;
?>