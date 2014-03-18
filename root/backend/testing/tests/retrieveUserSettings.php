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
				"enabled" => array("regex /1|0/"),
				"maxAudioBitrate" => array("int"),
				"maxVideoBitrate" => array("int"),
				"maxBandwidth" => array("int"),
				"enableTrafficLimit" => array("regex /1|0/"),
				"trafficLimit" => array("int", "lbound 0"),
				"trafficLimitPeriod" => array("int", "lbound 0"),
				"trafficUsed" => array("int", "lbound 0"),
				"timeToReset" => array("int"),

				"/permissions/general/*/idAction"	=> array("int"),
				"/permissions/general/*/displayName"	=> array("notblank"),
				"/permissions/general/*/granted"	=> array("regex /Y|N/"),

				"/permissions/mediaSources/*/idMediaSource"	=> array("int"),
				"/permissions/mediaSources/*/displayName"	=> array("notblank"),
				"/permissions/mediaSources/*/granted"		=> array("regex /Y|N/"),
				
				"/permissions/general/*/granted"	=> array("regex /Y|N/"),
				"/permissions/general/*/displayName"	=> array("notblank"),
				"/permissions/general/*/granted"	=> array("regex /Y|N/"),

				"/permissions/streamers/*/idStreamer"	=> array("int"),
				"/permissions/streamers/*/displayName"	=> array("notblank"),
				"/permissions/streamers/*/granted"	=> array("regex /Y|N/"),

			),
		)
	);

?>
