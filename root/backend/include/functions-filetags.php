<?php

/**
* function to extract tag data from a file. 
*/
function getFileTags($file)
{
	$getID3 = new getID3();

	$fileID3Data = $getID3->analyze($file);
	getid3_lib::CopyTagsToComments($fileID3Data);
	//var_dump_pre($fileID3Data);
	
	if(!isset($fileID3Data["comments_html"]))//cannot get tags	
	{
		return false;
	}

	$tags["artist"] = getTag("artist", $fileID3Data);
	$tags["album"] = getTag("album", $fileID3Data);
	$tags["albumartist"] = getTag("albumartist", $fileID3Data);
	$tags["tracknum"] = getTag("track", $fileID3Data);
	$tags["year"] = getTag("year", $fileID3Data);
	$tags["title"] = getTag("title", $fileID3Data);
	$tags["discnum"] = getTag("part_of_a_set", $fileID3Data);
	$tags["genre"] = getTag("genre", $fileID3Data);
	
	$tags["albumart"] = getAlbumArt($fileID3Data);
	
	$returnTags = array();
	foreach($tags as $tag => $value) // remove tags which do not exist
	{
		if($value)
			$returnTags[$tag] = $value;
	}

	//var_dump_pre($fileID3Data["id3v2"]["APIC"]);
		


	//var_dump_pre($fileID3Data);
	return $returnTags;
}


/**
* function to get a named tag out of an getID3 structure which has had CopyTagsToComments called
*/
function getTag($tag, &$id3data)
{
	if(isset($id3data["comments_html"][$tag]))
	{
		return $id3data["comments_html"][$tag][count($id3data["comments_html"][$tag])-1] ;
	}
	else
		return null;
}

/**
* function to reteive album art from tags if exists and base64 encode it
*/
function getAlbumArt(&$id3data){
	if(isset($id3data['id3v2'])&& isset($id3data['id3v2']["APIC"]) && isset($id3data['id3v2']["APIC"][0]) && isset($id3data['id3v2']["APIC"][0]["data"]))
	{
		return base64_encode($id3data['id3v2']["APIC"][0]["data"]);
	}
	else
	{
		return null;
	}
}

/**
* function to recurse through $tagsToExtract pulling out te tags in $fileID3Data
*/
/*
function extractTags($tagsToExtract, &$fileID3Data)
{	
	$tagsToReturn = array();
	foreach($tagsToExtract as $key => $val)
	{
		if(isset($fileID3Data[$key]))//if the tags are not there move on
		{
			if(is_array($val))
			{
				appLog("recursing into key '" . $key . "'");				
				$tagsToReturn[$key] = extractTags($val, $fileID3Data[$key]); 				
			}
			else
			{
				appLog("adding key '". $key . "' with data '" . $fileID3Data[$key] . "'");
				$tagsToReturn[$key] = $fileID3Data[$key];
			}
		}
	}
	return $tagsToReturn;
}*/
?>