<?php
	registerTest(
		//checks
		"search",  

		//get args
		array(
			"dir" => "/",
			"mediaSourceID" => 1, //hopefully there's a 1
			"query"	=> "test",
		),

		//checks
		array(
			"statusCodes" => array(
				"pass" => "^200$"
			),	
			"json"  => array(
				"/*/mediaSourceID"   => array("int"),
				"/*/results" => array("array"),

				"/*/results/dirs/*/path" => array("string"),
				"/*/results/dirs/*/name" => array("notblank"),

				"/*/results/files/*/fileObject"         => array("array"),
				"/*/results/files/*/fileObject/filename"         => array("notblank"),
				"/*/results/files/*/fileObject/displayName"         => array("notblank"),

				"/*/results/files/*/streamers/*/extension" => array("notblank"),
				"/*/results/files/*/streamers/*/streamerID" => array("int"),
				"/*/results/files/*/streamers/*/mediaType" => array("regex /[av]/"),
			),
		)
	);

?>
