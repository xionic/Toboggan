<?php

	//modify one
	registerTest(
		//checks
		"saveFileTypeSettings",  

		//get args
		array(
		),

		//checks
		array(
			"statusCodes" => array(
				"pass" => "^20(0|4)$"
			),	
			"sql" => array(
				"query" =>	 "select * from FileType",
				"validate" => array(
					array(
						"idfileType"	=> 1,
						"extension"	=> "mp3",
						"mimeType"	=> "audio/mp3",
						"mediaType"	=> "a",
						"idbitrateCmd"	=> 2,
						"iddurationCmd" => 3
					),
					array(
						"idfileType"	=> 2,
						"extension"	=> "wma",
						"mimeType"	=> "audio/wma",
						"mediaType"	=> "a",
						"idbitrateCmd"	=> 2,
						"iddurationCmd" => 3
					),
					array(
						"idfileType"	=> 3,
						"extension"	=> "flv",
						"mimeType"	=> "video/flv",
						"mediaType"	=> "v",
						"idbitrateCmd"	=> null,
						"iddurationCmd" => null
					),
					array(
						"idfileType"	=> 4,
						"extension"	=> "avi",
						"mimeType"	=> "video/avit",
						"mediaType"	=> "v",
						"idbitrateCmd"	=> 2,
						"iddurationCmd" => 3
					),					
				)
			)
		),

		//do login?
		true,

		//Post args
		array (
			"settings" => json_encode(
				array(
					array(
						"fileTypeID"	=> 1,
						"extension"	=> "mp3",
						"mimeType"	=> "audio/mp3",
						"mediaType"	=> "a",
						"bitrateCmdID"	=> 2,
						"durationCmdID" => 3
					),
					array(
						"fileTypeID"	=> 2,
						"extension"	=> "wma",
						"mimeType"	=> "audio/wma",
						"mediaType"	=> "a",
						"bitrateCmdID"	=> 2,
						"durationCmdID" => 3
					),
					array(
						"fileTypeID"	=> 3,
						"extension"	=> "flv",
						"mimeType"	=> "video/flv",
						"mediaType"	=> "v",
						"bitrateCmdID"	=> null,
						"durationCmdID" => null
					),
					array(
						"fileTypeID"	=> 4,
						"extension"	=> "avi",
						"mimeType"	=> "video/avit",
						"mediaType"	=> "v",
						"bitrateCmdID"	=> 2,
						"durationCmdID" => 3
					),
				)
			)
		)
	);

	//default values
	registerTest(
		//checks
		"saveFileTypeSettings",  

		//get args
		array(
		),

		//checks
		array(
			"statusCodes" => array(
				"pass" => "^20(0|4)$"
			),	
			"sql" => array(
				"query" =>	 "select * from FileType",
				"validate" => array(
					array(
						"idfileType"	=> 1,
						"extension"	=> "mp3",
						"mimeType"	=> "audio/mp3",
						"mediaType"	=> "a",
						"idbitrateCmd"	=> 2,
						"iddurationCmd" => 3
					),
					array(
						"idfileType"	=> 2,
						"extension"	=> "wma",
						"mimeType"	=> "audio/wma",
						"mediaType"	=> "a",
						"idbitrateCmd"	=> 2,
						"iddurationCmd" => 3
					),
					array(
						"idfileType"	=> 3,
						"extension"	=> "flv",
						"mimeType"	=> "video/flv",
						"mediaType"	=> "v",
						"idbitrateCmd"	=> null,
						"iddurationCmd" => null
					),
					array(
						"idfileType"	=> 4,
						"extension"	=> "avi",
						"mimeType"	=> "video/avi",
						"mediaType"	=> "v",
						"idbitrateCmd"	=> 2,
						"iddurationCmd" => 3
					),
				)
			)
		),

		//do login?
		true,

		//Post args
		array (
			"settings" => json_encode(
				array(
					array(
						"fileTypeID"	=> 1,
						"extension"	=> "mp3",
						"mimeType"	=> "audio/mp3",
						"mediaType"	=> "a",
						"bitrateCmdID"	=> 2,
						"durationCmdID" => 3
					),
					array(
						"fileTypeID"	=> 2,
						"extension"	=> "wma",
						"mimeType"	=> "audio/wma",
						"mediaType"	=> "a",
						"bitrateCmdID"	=> 2,
						"durationCmdID" => 3
					),
					array(
						"fileTypeID"	=> 3,
						"extension"	=> "flv",
						"mimeType"	=> "video/flv",
						"mediaType"	=> "v",
						"bitrateCmdID"	=> null,
						"durationCmdID" => null
					),
					array(
						"fileTypeID"	=> 4,
						"extension"	=> "avi",
						"mimeType"	=> "video/avi",
						"mediaType"	=> "v",
						"bitrateCmdID"	=> 2,
						"durationCmdID" => 3
					),
				)
			)
		)
	);
	
	//delete id 2
	/*registerTest(
		//checks
		"saveFileTypeSettings",  

		//get args
		array(
		),

		//checks
		array(
			"statusCodes" => array(
				"pass" => "^20(0|4)$"
			),	
			"sql" => array(
				"query" =>	 "select * from FileType",
				"validate" => array(
					array(
						"idfileType"	=> 1,
						"extension"	=> "mp3",
						"mimeType"	=> "audio/mp3",
						"mediaType"	=> "a",
						"idbitrateCmd"	=> 2,
						"iddurationCmd" => 3
					),
					array(
						"idfileType"	=> 3,
						"extension"	=> "flv",
						"mimeType"	=> "video/flv",
						"mediaType"	=> "v",
						"idbitrateCmd"	=> null,
						"iddurationCmd" => null
					),
					array(
						"idfileType"	=> 4,
						"extension"	=> "avi",
						"mimeType"	=> "video/avi",
						"mediaType"	=> "v",
						"idbitrateCmd"	=> 2,
						"iddurationCmd" => 3
					),
				)
			)
		),

		//do login?
		true,

		//Post args
		array (
			"settings" => json_encode(
				array(
					array(
						"fileTypeID"	=> 1,
						"extension"	=> "mp3",
						"mimeType"	=> "audio/mp3",
						"mediaType"	=> "a",
						"bitrateCmdID"	=> 2,
						"durationCmdID" => 3
					),					
					array(
						"fileTypeID"	=> 3,
						"extension"	=> "flv",
						"mimeType"	=> "video/flv",
						"mediaType"	=> "v",
						"bitrateCmdID"	=> null,
						"durationCmdID" => null
					),
					array(
						"fileTypeID"	=> 4,
						"extension"	=> "avi",
						"mimeType"	=> "video/avi",
						"mediaType"	=> "v",
						"bitrateCmdID"	=> 2,
						"durationCmdID" => 3
					),
				)
			)
		)
	);*/
	
	

?>
