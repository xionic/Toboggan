<?php
	registerTest(
		//checks
		"retrieveCommandSettings",  

		//get args
		array(
		),

		//checks
		array(
			"statusCodes" => array(
				"pass" => "^200$"
			),
			"json" => array(
				"schema" => array("array"),
				"/data/*/commandID" => array("int"),
				"/data/*/command" => array("notblank"),
				"/data/*/displayName" => array("notblank"),
			)
		)
	);

?>
