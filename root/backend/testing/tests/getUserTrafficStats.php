<?php
	registerTest(
		//checks
		"getUserTrafficStats",  

		//get args
		array(
		),

		//checks
		array(
			"statusCodes" => array(
				"pass" => "^200$"
			),	
			"json"	=> array(
				"enableTrafficLimit" => array("regex /Y|N/"),
				"trafficLimit" => array("optional", "int", "lbound 1"),
				"trafficUsed" => array("optional", "int", "lbound 0"),
				"trafficLimitPeriod" => array("optional", "int", "lbound 1"),
				"timeToReset" => array("optional", "int"),
			),
		)
	);

?>
