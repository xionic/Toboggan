<?php
	registerTest(
		//checks
		"retrieveMediaSourceSettings",  

		//get args
		array(
		),

		//checks
		array(
			"statusCodes" => array(
				"pass" => "^200$"
			),	
			"json"	=> array(
				"/*/mediaSourceID" => array("int"),
				"/*/path" 		=> array("notblank"),
				"/*/displayName" 	=> array("notblank"),
			),
		)
	);

?>
