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
		)
	);

?>
