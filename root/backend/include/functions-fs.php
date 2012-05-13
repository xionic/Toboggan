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
		foreach(getMediaSources() as $source)
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
		$results[] = array(
			"mediaSourceID" => $id,
			"results" => getSearchResults($path,normalisePath($dir),$query, true),
		);
		
	}
	//var_dump_pre($results);
	restTools::sendResponse(json_encode($results), 200, "text/json");
}

/**
* Do a search and return a result structure - returns an array(dirResultsArray, fileResultsArray)
*/
function getSearchResults($mediaSourcePath, $relPath, $query, $recurse)
{
	$path = normalisePath(normalisePath($mediaSourcePath)."/".normalisePath($relPath))."/";

	if(!is_dir($path))
	{	
		appLog("path is not a directory: ".$path, appLog_INFO);
		return false;
	}
	$dirHandle = opendir($path);
	$fileResults = array();
	$dirResults = array();
	
	//loop through all "files" in the dir
	while(($file = readdir($dirHandle)) !== false)
	{
		$filepath = $path.$file;
		//echo $filepath."<br>";
		if(is_dir($filepath) && $file != "." && $file != "..") //directories
		{
			if($recurse)// recurse into subdirs
			{
				//echo "recusring into $filepath <br>";
				$subResults = getSearchResults($mediaSourcePath, $relPath."/".$file, $query, $recurse);  // do subdir search
				$dirResults = array_merge($dirResults, $subResults["dirs"]);// merge results into our results array - flat structure	
				$fileResults = array_merge($fileResults, $subResults["files"]);
			}
			//check the dir name
			if(stristr($filepath, $query) !== false)
			{
				$dirResults[] = array( // add to results
					"path"	=> $relPath,
					"name"	=> $file,
				);
			}
		}
		elseif(is_file($filepath)) // check file against queried string
		{//echo stristr($filepath, $query);
			if(stristr($filepath, $query) !== false)
			{
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




?>
