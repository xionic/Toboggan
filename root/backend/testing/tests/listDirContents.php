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
				"CurrentPath"	=> array("string"),
				"Directories"	=> array("array"),
				"Files"		=> array("array"),
			),
		)
	);

?>
