<?php
	registerTest(
		//checks
		"downloadFile",  

		//get args
		array(
			"dir" => "/test/",
			"filename" => "440Hz-5sec.mp3",
			"mediaSourceID" => 1,
		),	

		//checks
		array(
			"statusCodes" => array(
				"pass" => "^200$"
			),				
		)
	);

?>
