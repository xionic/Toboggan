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
	
);

?>
