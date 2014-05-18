<?php
	registerTest(
		//checks
		"getAddUserSchema",  

		//get args
		array(
		),

		//checks
		array(
			"statusCodes" => array(
				"pass" => "^200$"
			),
			"json" => array(
				"schema" => array("array"), //cba validating schema
			)
		)
	);

?>
