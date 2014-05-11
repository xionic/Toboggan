<?php
	registerTest(
		//checks
		"retrieveFileConverterSettings",  

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
				"/data/*/fileConverterID" => array("int"),
				"/data/*/fromFileTypeID" => array("int"),
				"/data/*/toFileTypeID" => array("int"),
				"/data/*/commandID" => array("int"),
			)
		)
	);

?>
