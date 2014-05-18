<?php
	registerTest(
		//checks
		"saveClientSettings",  

		//get args
		array(
		),

		//checks
		array(
			"statusCodes" => array(
				"pass" => "^20(0|4)$"
			),	
		),

		//do login?
		true,

		//Post args
		array (
			"settingsBlob" => "test harness settings"
		)
	);

?>
