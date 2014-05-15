<html>
<head>
	<link rel="stylesheet" type="text/css" href="style.css" />
	<script src="https://code.jquery.com/jquery-2.1.0.min.js" type="text/javascript"></script>
	<title>Toboggan test harness</title>
</head>
<body>

	<button onclick='$(".fullResponse").toggle();'>Show/Hide Full Responses</button>
	<button onclick='$(".fullChecks").toggle();'>Show/Hide Check Details</button>

<?php

	include("functions-testing.php");
	
	//include tests
	foreach(glob("./tests/*.php") as $file){
		include($file);
	}

	$actionsToTest = null;	
	if(isset($_GET["action"]))
		$actionsToTest[] = $_GET["action"];
	
	runTests($actionsToTest);
	displayResultsTable();



?>
</body>
</html>
