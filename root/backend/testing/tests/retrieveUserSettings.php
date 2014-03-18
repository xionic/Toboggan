<?php
	registerTest(
		//checks
		"retrieveUserSettings",  

		//get args
		array(
			"userid" => 1,
		),

		//checks
		array(
			"statusCodes" => array(
				"pass" => "^200$"
			),	
			"json"	=> array(
				"idUser" => array("notblank"),
				"username" => array("notblank"),
				"email" => array("string"),
				"enabled" => array("regex /Y|N/"),
				"maxAudioBitrate" => array("int"),
				"maxVideoBitrate" => array("int"),
				"maxBandwidth" => array("int"),
				"enableTrafficLimit" => array("regex /Y|N/"),
				"trafficLimit" => array("int", "lbound 0"),
				"trafficLimitPeriod" => array("int", "lbound 0"),
				"trafficUsed" => array("int", "lbound 0"),
				"timeToReset" => array("int"),

				"/permissions/general/*/id"	=> array("int"),
				"/permissions/general/*/displayName"	=> array("notblank"),
				"/permissions/general/*/granted"	=> array("regex /Y|N/"),

				"/permissions/mediaSources/*/id"	=> array("int"),
				"/permissions/mediaSources/*/displayName"	=> array("notblank"),
				"/permissions/mediaSources/*/granted"		=> array("regex /Y|N/"),
				
				"/permissions/streamers/*/id"	=> array("int"),
				"/permissions/streamers/*/displayName"	=> array("notblank"),
				"/permissions/streamers/*/granted"	=> array("regex /Y|N/"),

			),
		)
	);

?>
