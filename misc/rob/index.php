<!DOCTYPE html> 
<html>
<head>
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8' lang='en-gb' />
	<title>MP3 Listing</title>
	<link href='./css/style.css' rel='stylesheet' type='text/css' />
</head>

<body>
	<div id='fileContainer' >
	<?php
//	define(ROOT_DIR,"/var/www/music_files/");
	define('ROOT_DIR', "/media/WD-250_md0/music/");

	$dir = html_entity_decode(base64_decode($_GET['dir']));

	if(strlen(strstr($dir,".."))>0 || $dir[0]=='/' | $dir=="")
		$dir = ".";

	if(substr($dir,-1)!="/")
		$dir .= "/";

	$dh = opendir(ROOT_DIR.$dir) or die("opendir failed:".ROOT_DIR.$dir);

	$files	= array();
	$dirs	= array();
	$links	= array();

//	set_time_limit (5);

	while (($occurrence = readdir($dh)) !== false)
	{
		if($occurrence == "." || $occurrence == "index.php")
		{
			continue;
		}

//		var_dump($occurrence);

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

	natcasesort($dirs);
	natcasesort($files);
	natcasesort($links);

	require_once('./getid3/getid3.php');

	foreach($dirs as $thisdir)
	{
		$thisdir = htmlentities($thisdir);

		echo "<div class='dir object' >";
		echo "<p><a href='?dir=".base64_encode($dir.$thisdir)."'>";

		if($thisdir=="..")
		{
			echo "<img class='dir' src='./img/upDir.png' alt='directory' />";
			echo $thisdir = "Up Dir";
		}
		else
		{
			echo "<img class='dir' src='./img/dir.png' alt='directory' />";
			echo $thisdir;
		}
		echo "</a></p>";
		echo "</div>";
	}

	$getID3 = new getID3;
	foreach($files as $file)
	{
		$ThisFileInfo = $getID3->analyze(ROOT_DIR.$dir.$file);

		$id3v2Tags 		= $ThisFileInfo['tags']['id3v2'];
		$picStruct 		= $ThisFileInfo['id3v2']['APIC'][0];

		$albumArt 		= $picStruct['data'];
		$albumArtMime 	= $picStruct['mime'];

		echo "<div class='mp3 object' >";
//		echo "<p><a href='getFileContents.php?file=".$dir.$file."'>";
		echo "<p><a href='fplayer.php?file=".$dir.$file."'>";

		if($albumArt!==NULL)
		{
			$imgHash = md5($albumArt);
			$imgFile = "./tmp/".$imgHash.".jpg";
			//check file existance & create if not
			if(!file_exists($imgFile))
				file_put_contents($imgFile,$albumArt);

		}
		else
		{
			$imgFile = "./img/mp3-icon.png";
		}
		echo "<img class='albumArt' src='{$imgFile}' />";

		echo htmlentities("{$id3v2Tags['artist'][0]} - {$id3v2Tags['title'][0]}")."</a></p>";
		echo "</div>";
	}
	?>
	</div>
</body>
</html>
