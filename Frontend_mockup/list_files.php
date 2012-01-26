<?php

/** Returns a JSON object representing the current folder contents **/

	header("Content-type: application/json");

	$inputDir = isset($_GET['dir'])?$_GET['dir']:"";
	
	//define('ROOT_DIR', "/media/WD-250_md0/music/");
	define('ROOT_DIR', "/var/wwws/code/ultrasonic/ultrasonic/Frontend_mockup/");

	$dir = html_entity_decode(urldecode($inputDir));

	if(strlen(strstr($dir,".."))>0 || $dir=="" || $dir[0]=='/' )
		$dir = ".";

	if(substr($dir,-1)!="/")
		$dir .= "/";

	$dh = opendir(ROOT_DIR.$dir) or die("opendir failed:".ROOT_DIR.$dir);

	$files	= array();
	$dirs	= array();
	$links	= array();
	
	while (($occurrence = readdir($dh)) !== false)
	{
		if($occurrence == "." || $occurrence == "index.php")
		{
			continue;
		}

		switch(filetype(ROOT_DIR.$dir.$occurrence))
		{
			case 'dir':
				$dirs[] 		= $occurrence;
				break;
			case 'file':
				$files[] 		= $occurrence;
				break;
			case 'link':
				$links[]		= $occurrence;
				break;
		}
	}
	closedir($dh);

	//TODO:: these should be made to be case insensitive really
	sort($dirs);		
	sort($files);
	sort($links);
		
	//var_dump($dirs);	
		
	echo json_encode(
			array("CurrentPath" => $dir, "Directories" => $dirs, "Files" => $files)
		);	
?>