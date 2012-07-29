<?php
/**
* get a JSON representation of the contents of directories
*/
function outputDirContents_JSON($dir, $mediaSourceID){

	//check inputs
	if(((int)$mediaSourceID)==0)
	{
		restTools::sendResponse("Invalid or missing mediaSourceID", 404, "text/plain");
	}
	

	$mediaSourcePath = getMediaSourcePath($mediaSourceID);
	if(!$mediaSourcePath)
	{
		reportError("No media sources defined");
	}
		
	$mediaSourcePath = normalisePath($mediaSourcePath)."/";
	$dir = normalisePath($dir)."/";
	
	//check that the path is a dir
	if(!is_dir($mediaSourcePath.$dir))
	{
		reportError("Directory does not exist");
	}

	$dh = opendir($mediaSourcePath.$dir) or reportServerError("opendir failed:".$mediaSourcePath.$dir);

	$files	= array();
	$dirs	= array();
	$links	= array();
	
	while (($occurrence = readdir($dh)) !== false)
	{
		if($occurrence == "." || $occurrence == "..")
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

	//sort the bitches
	usort($dirs, 'strcasecmp');

	usort($files, function($a,$b) {
		return strcasecmp($a['displayName'], $b['displayName']);
	});

	restTools::sendResponse(
		json_encode(
			array("CurrentPath" => $dir, "Directories" => $dirs, "Files" => $files)
		),
		200
	);
}

/**
* outputs search results
*/
function outputSearchResults_JSON($mediaSourceID, $dir, $query)
{
	$allMediaSources = $mediaSourceID == "all" ? true:false;
	
	//build a list of media sources to search through
	$mediaSourceArr = array();
	if($allMediaSources) // search all mediaSources
	{
		$mediaSourcesList = getMediaSources();
		foreach($mediaSourcesList as $source)
		{
			$mediaSourceArr[] = $source["mediaSourceID"];
		}
	}
	else // only one mediaSource requested
	{
		$mediaSourceArr[] = $mediaSourceID;
	}
	
	$results = array();
	//loop through the mediaSources and do the search
	foreach($mediaSourceArr as $id)
	{
		$path = getMediaSourcePath($id);
		if(!$path){
			reportError("Invalid/Non-existant media source");
			die;
		}
				
		$resultSet = getSearchResults($path,$dir,$query, true);
		if($resultSet === false)
		{
			reportError("Non existant directory", 400);
		}
		
		usort($resultSet['files'], function($a,$b){
			return strcasecmp($a['path'], $b['path'])!==0?strcasecmp($a['path'], $b['path']):strcasecmp($a['fileObject']['displayName'], $b['fileObject']['displayName']);
		});
		
		$results[] = array(
			"mediaSourceID" => $id,
			"results" => $resultSet,
		);
		
	}
	restTools::sendResponse(json_encode($results), 200, JSON_MIME_TYPE);
}

/**
* Do a search and return a result structure - returns an array(dirResultsArray, fileResultsArray)
*/
function getSearchResults($mediaSourcePath, $relPath, $query, $recurse)
{
	$relPath = normalisePath($relPath);
	$path = $mediaSourcePath."/".$relPath."/";

	if(!is_dir($path))
	{	
		reportError("path is not a directory: ".$path, 400);
		return false;
	}
	$dirHandle = opendir($path);
	$fileResults = array();
	$dirResults = array();
	
	//loop through all "files" in the dir
	while(($FSObj = readdir($dirHandle)) !== false)
	{
		$filepath = $path.$FSObj;
		
		//handle directories in current dir
		if(is_dir($filepath) && $FSObj != "." && $FSObj != "..") 
		{
			if($recurse)// recurse into subdirs
			{
				$subResults = getSearchResults($mediaSourcePath, $relPath."/".$FSObj, $query, $recurse);  // do subdir search
				
				// merge sub-file and sub-directory results into our local results array
				$dirResults = array_merge($dirResults, $subResults["dirs"]);
				$fileResults = array_merge($fileResults, $subResults["files"]);
			}
			// after recursing, check the dir name for match against query
			if(stristr($FSObj, $query) !== false)
			{
				//add it to results if it matched
				$dirResults[] = array( 
					"path"	=> $relPath,
					"name"	=> $FSObj,
				);
			}
		}
		//handle files in current dir
		elseif(is_file($filepath)) 
		{	//match filename against query
			if(stristr($FSObj, $query) !== false)
			{
				//add to result set
				$fileResults[] = array(
					"path" 	=> $relPath,
					"fileObject" => getFileObject($filepath),
				);
			}
		}
	}

	closedir($dirHandle);	
	return array("dirs" => $dirResults, "files" => $fileResults);
}

/**
* Output the last NBytes of the server's application log
*/
function outputApplicationLog_JSON($lastNBytes)
{
	$file = getConfigItem("logFile");
	
	$sizeInBytes = filesize($file);
	
	$fh = fopen($file, 'r');
	
	$startBytes = $sizeInBytes - $lastNBytes;
	if($startBytes < 0) // start at the beginning if file is shorter than the length requested
		$startBytes = 0;
		
	fseek($fh, $startBytes);
	
	$results = array("logFileText" => fread($fh, $lastNBytes));
	
	restTools::sendResponse(json_encode($results), 200, JSON_MIME_TYPE);
	
}




?>
