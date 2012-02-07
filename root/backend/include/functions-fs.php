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
		if($occurrence == ".")
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
		$results[] = array(
			"mediaSourceID" => $id,
			"results" => getSearchResults($path,normalisePath($dir),$query, true),
		);
		//return json_encode(getSearchResults($path.normalisePath($dir),$query, true));
	}
	restTools::sendResponse(json_encode($results), 200, "text/json");
}

/**
* Do a search and return a result structure
*/
function getSearchResults($mediaSourcePath, $relPath, $query, $recurse)
{
	$path = normalisePath(normalisePath($mediaSourcePath)."/".normalisePath($relPath))."/";
	//echo "path: $path <br>";
	if(!is_dir($path))
	{	
		appLog("path is not a directory: ".$path, appLog_INFO);
		return false;
	}
	$dirHandle = opendir($path);
	$results = array();
	
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
				$results = array_merge($results, getSearchResults($mediaSourcePath, $relPath."/".$file, $query, $recurse)); // merge results into our results array - flat structure	
			}
			//check the dir name
			if(stristr($filepath, $query) !== false)
			{
				$results[] = array( // add to results
					"type"	=> "dir",
					"path"	=> $relPath,
					"name"	=> $file,
				);
			}
		}
		elseif(is_file($filepath)) // check file against queried string
		{//echo stristr($filepath, $query);
			if(stristr($filepath, $query) !== false)
			{
				$results[] = array( // add to results
					"type"	=> "file",
					"path"	=> $relPath,
					"name"	=> $file,
				);
			}
		}
	}
	
	closedir($dirHandle);
	return $results;
}




?>