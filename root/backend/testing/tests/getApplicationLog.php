<?php
	registerTest(
		//checks
		"getApplicationLog",  

		//get args
		array(),	

		//checks
		array(
			"statusCodes" => array(
				"pass" => "^200$"
			),	
			"json"	=> array(
				"logFileText" => array("string")
			),
		)
	);

	registerTest(
		//checks
		"getApplicationLog",  

		//get args
		array(),	

		//checks
		array(
			"statusCodes" => array(
				"pass" => "^401$"
			),	
		),
		false //no login
	);
?>
