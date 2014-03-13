<?php
	registerTest(
		//checks
		"retrieveClientSettings",  

		//get args
		array(
		),

		//checks
		array(
			"statusCodes" => array(
				"pass" => "^20(0|4)$"
			),	
			"json"	=> array(
				"settingsBlob" 	=> array("string"),
			),
		)
	);

?>
