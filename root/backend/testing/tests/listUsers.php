<?php
	registerTest(
		//checks
		"listUsers",  

		//get args
		array(
		),

		//checks
		array(
			"statusCodes" => array(
				"pass" => "^200$"
			),	
			"json"	=> array(
				"/*/idUser" => array("notblank"),
				"/*/username" => array("notblank"),
			),
		)
	);

?>
