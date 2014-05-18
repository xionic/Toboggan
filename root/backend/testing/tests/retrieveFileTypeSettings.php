<?php
	registerTest(
		//checks
		"retrieveFileTypeSettings",  

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
				"/data/*/fileTypeID" => array("int"),
				"/data/*/extension" => array("notblank"),
				"/data/*/mimeType" => array("notblank"),
				"/data/*/mediaType" => array("regex /[av]/"),
				"/data/*/bitrateCmdID" => array(function($a){return (is_int($a+0) || is_null($a));}),
				"/data/*/durationCmdID" => array(function($a){return (is_int($a+0) || is_null($a));}),
			)
		)
	);

?>
