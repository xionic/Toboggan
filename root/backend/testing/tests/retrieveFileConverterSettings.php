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
				"/*/fileConverterID" => array("int"),
				"/*/fromFileType" => array("notblank"),
				"/*/toFileType" => array("notblank"),
				"/*/commandID" => array("int"),
			)
		)
	);

?>
