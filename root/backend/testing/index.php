<html>
<head>
	<link rel="stylesheet" type="text/css" href="style.css" />
	<script src="https://code.jquery.com/jquery-2.1.0.min.js" type="text/javascript"></script>
	<title>Toboggan test harness</title>
</head>
<body>
	<form method="POST" action ="">
		<label for="testusername">Test username</label>
		<input id="testusername"  name="username" />
		<label for="testpassword">Test password</label>
		<input type="password" id="testpassword" name="password" />
		<input type="submit" />
	</form>

	<button onclick='$(".fullResponse").toggle();'>Show/Hide Full Responses</button>
	<button onclick='$(".fullChecks").toggle();'>Show/Hide Check Details</button>

<?php

	if(isset($_POST) && isset($_POST["username"]) && isset($_POST["password"])){

		include("functions-testing.php");

		//override config
		$config["testUser"] = $_POST["username"];
		$config["testPass"] = base64_encode(hash("sha256",$_POST["password"],true));
		
		//include tests
		foreach(glob("./tests/*.php") as $file){
			include($file);
		}

		$actionsToTest = null;	
		if(isset($_GET["action"]))
			$actionsToTest[] = $_GET["action"];
		
		runTests($actionsToTest);
		displayResultsTable();
	}

?>
</body>
</html>
