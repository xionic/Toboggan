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
</head>
<body>
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
