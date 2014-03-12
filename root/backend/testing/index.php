<html>
<head>
	<style type="text/css">
		table, tr, td{
			border-collapse:collapse;
		}
		td, th{
			border: solid 1px red;
		}
	</style>
	<script src="https://code.jquery.com/jquery-2.1.0.min.js" type="text/javascript"></script>
</head>
<body>

	<a onclick='$(".fullResponse").toggle();'>Show/Hide Full Responses</a>

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
