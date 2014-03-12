<?php
	registerTest(
		//checks
		"listMediaSources",  

		//get args
		array(),

		//checks
		array(
			"statusCodes" => array(
				"pass" => "^200$"
			),	
			"json"	=> array( // bo constraints forces entire arg to be array
			),
		)
	);

?>
