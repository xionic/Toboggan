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
				"/data/*/fromFileType" => array("notblank"),
				"/data/*/toFileType" => array("notblank"),
				"/data/*/commandID" => array("int"),
			)
		)
	);

?>
