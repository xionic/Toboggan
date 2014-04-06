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
				"/*/commandID" => array("int"),
				"/*/command" => array("notblank"),
				"/*/displayName" => array("notblank"),
			)
		)
	);

?>
