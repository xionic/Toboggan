<?php

$schema["retrieveFileTypeSettings"] = array(
	array(
			"fileTypeID" => array(
			"displayName"=> "File Type ID",
			"type"=> "int",
			"readonly" => true
		),
		"extension" => array(
			"displayName"=> "Extension",
			"type"=> "text",
			"readonly" => false
		),
		"mimeType" => array(
			"displayName"=> "MIME Type",
			"type"=> "text",
			"readonly" => false
		),
		"mediaType" => array(
			"displayName"=> "Type of media (a or v)",
			"type"=> "text",
			"readonly" => false
		),
		"bitrateCmdID" => array(
			"displayName"=> "ID of bitrate command",
			"type"=> "int",
			"readonly" => false
		),
		"durationCmdID" => array(
			"displayName"=> "ID of duration command",
			"type"=> "int",
			"readonly" => false
		),
	)
);

$schema["retrieveCommandSettings"] = array(
	array(
		"commandID" => array(
			"displayName"=> "ID of the command",
			"type"=> "text",
			"readonly" => true
		),
		"command" => array(
			"displayName"=> "Command",
			"type"=> "text",
			"readonly" => false
		),
		"displayName" => array(
			"displayName"=> "Description",
			"type"=> "text",
			"readonly" => false
		),
	)
);

$schema["retrieveFileConverterSettings"] = array(
	array(
		"fileConverterID" => array(
			"displayName"=> "ID of the the converter",
			"type"=> "text",
			"readonly" => true
		),
		"fromFileType" => array(
			"displayName"=> "Extension to convert from",
			"type"=> "int",
			"readonly" => false
		),
		"toFileType" => array(
			"displayName"=> "Extension to convert to",
			"type"=> "int",
			"readonly" => false
		),
		"commandID" => array(
			"displayName"=> "ID of conversion command",
			"type"=> "text",
			"readonly" => false
		),
	)
);

$schema["getAddUserSchema"] = array(
	"username" => array(
		"displayName"=> "Username",
		"type"=> "text",
		"readonly" => false
	),
	"password" => array(
		"displayName"=> "Password",
		"type"=>"password",
		"readonly"=> false
	),
	"email" => array(
		"displayName"=> "Email Address",
		"type"=> "text",
		"readonly" => false
	),
	"enabled" => array(
		"displayName"=> "Enabled",
		"type"=> "boolean",
		"readonly" => false
	),
	"maxAudioBitrate" => array(
		"displayName"=> "Max Audio Bitrate",
		"type"=> "int",
		"readonly" => false
	),
	"maxVideoBitrate" => array(
		"displayName"=> "Max Video Bitrate",
		"type"=> "int",
		"readonly" => false
	),
	"maxBandwidth" => array(
		"displayName"=> "Max Bandwidth",
		"type"=> "int",
		"readonly" => false
	),
	"enableTrafficLimit" => array(
		"displayName"=> "Enable traffic Limit",
		"type"=> "boolean",
		"readonly" => false
	),
	"trafficLimit" => array(
		"displayName"=> "Traffic limit",
		"type"=> "int",
		"readonly" => false
	),
	"trafficLimitPeriod" => array(
		"displayName"=> "Traffic limit reset time ",
		"type"=> "int",
		"readonly" => false
	),
	"trafficUsed" => array(
		"displayName"=> "Traffic currently used",
		"type"=> "int",
		"readonly" => false
	),
	"timeToReset" => array(
		"displayName"=> "Time to traffic limit reset",
		"type"=> "int",
		"readonly" => false
	),
	"permissions" => array(
		"general" => array(
			"id" => array(
				"displayName"=> "ID of permission",
				"type"=> "int",
				"readonly" => true
			),
			"displayName" => array(
				"displayName"=> "Permission",
				"type"=> "text",
				"readonly" => true
			),
			"granted" => array(
				"displayName"=> "Granted?",
				"type"=> "text",
				"readonly" => false
			),
		),
		"mediaSources" => array(
			"id" => array(
				"displayName"=> "ID of media source",
				"type"=> "int",
				"readonly" => true
			),
			"displayName" => array(
				"displayName"=> "Media Source",
				"type"=> "text",
				"readonly" => true
			),
			"granted" => array(
				"displayName"=> "Granted?",
				"type"=> "text",
				"readonly" => false
			),
		),
		"fileConverters" => array(
			"id" => array(
				"displayName"=> "ID of file converter",
				"type"=> "int",
				"readonly" => true
			),
			"displayName" => array(
				"displayName"=> "File Converter",
				"type"=> "text",
				"readonly" => true
			),
			"granted" => array(
				"displayName"=> "Granted?",
				"type"=> "text",
				"readonly" => false
			),
		),
	),
);

?>
