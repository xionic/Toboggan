<?php
/**
* get a JSON representation of the contents of directories
*/
function getDirContents_JSON($dir, $mediaSourceID){

	//check inputs
	if(((int)$mediaSourceID)==0)
	{
		restTools::sendResponse("Invalid or missing mediaSourceID", 404, "text/plain");
	}

	$mediaSourcePath = normalisePath(getMediaSourcePath($mediaSourceID))."/";
	$dir = normalisePath($dir);

	if(substr($dir,-1)!="/")
		$dir .= "/";

	$dh = opendir($mediaSourcePath.$dir) or die("opendir failed:".$mediaSourcePath.$dir);

	$files	= array();
	$dirs	= array();
	$links	= array();
	
	while (($occurrence = readdir($dh)) !== false)
	{
		if($occurrence == "." || $occurrence == ".." || $occurrence == "index.php" )
		{
			continue;
		}

		switch(filetype($mediaSourcePath.$dir.$occurrence))
		{
			case 'dir':
				$dirs[] 		= $occurrence;
				break;
			case 'file':
				$files[]		= getFileObject($dir.$occurrence);
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
		
	restTools::sendResponse(
		json_encode(
			array("CurrentPath" => $dir, "Directories" => $dirs, "Files" => $files)
		),
		200
	);
}

?>