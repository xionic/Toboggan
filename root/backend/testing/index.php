<html>
<head>
	<link rel="stylesheet" type="text/css" href="style.css" />
	<script src="https://code.jquery.com/jquery-2.1.0.min.js" type="text/javascript"></script>
</head>
<body>

	<button onclick='$(".fullResponse").toggle();'>Show/Hide Full Responses</button>

<?php

	include("functions-testing.php");
	
	//include tests
	foreach(glob("./tests/*.php") as $file){
		include($file);
	}


	runTests();
	displayResultsTable();



?>
</body>
</html>
