<?php
	registerTest(
		//checks
		"ping",  

		//get args
		array(),	

		//checks
		array(
			"statusCodes" => array(
				"pass" => "^200$"
			),				
		)
	);
	registerTest(
		//checks
		"ping",  

		//get args
		array(),	

		//checks
		array(
			"statusCodes" => array(
				"pass" => "^401$"
			),				
		),
		false
	);

?>
