<?php
	registerTest(
		//checks
		"getStream",  

		//get args
		array(
			"dir" => "/test/",
			"filename" => "440Hz-5sec.mp3",
			"mediaSourceID" => 1,
			"fileConverterID" => 1,
		),	

		//checks
		array(
			"statusCodes" => array(
				"pass" => "^200$"
			),				
		)
	);

?>
