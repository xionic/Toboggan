<?php
	registerTest(
		//checks
		"listDirContents",  

		//get args
		array(
			"dir" => "/",
			"mediaSourceID" => 1, //hopefully there's a 1
		),

		//checks
		array(
			"statusCodes" => array(
				"pass" => "^200$"
			),	
			"json"	=> array(
				"CurrentPath"	=> array("notblank"),
				"Directories"	=> array("array"),
				"/Directories/*/" => array("notblank"),
				"Files"		=> array("array"),
				"/Files/*/filename" => array("notblank"),
				"/Files/*/displayName" => array("notblank"),
				"/Files/*/streamers/*/extension" => array("notblank"),
				"/Files/*/streamers/*/streamerID" => array("int"),
				"/Files/*/streamers/*/mediaType" => array("regex /[av]/"),
			),
		)
	);

?>
