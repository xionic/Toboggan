<?php
	registerTest(
		//checks
		"getFileMetadata",  

		//get args
		array(
			"dir" => "/",
			"mediaSourceID" => 1, //hopefully there's a 1
			"filename" => "test.mp3", 
		),

		//checks
		array(
			"statusCodes" => array(
				"pass" => "^200$"
			),	
			"json"	=> array(
				"filename"	=> array("string"),
				"displayName"	=> array("string"),
				"filesize"	=> array("lbound 0"),
				"duration"	=> array(function($a){return (is_int($a) && $a > 0) || $a == null;}), // can be null, and we cannot do "OR" :(
				"streamers"	=> array("array"),
				"tags"		=> array("array"),
			),
		)
	);

?>
