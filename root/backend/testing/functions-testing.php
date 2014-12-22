<?php

	require_once("../lib/PHPArgValidator/PHPArgValidator.class.php");

	$config["testAPIKey"] = "{05C8236E-4CB2-11E1-9AD8-A28BA559B8BC}";
	$config["testAPIVer"] = "0.6";
	$config["testUser"] = "autotest";
	$config["testPass"] =  base64_encode(hash("sha256","password", true));
	$config["basicAuth"] = @isset($_GET["bauser"]);
	$config["basicAuthUser"] = @$_GET["bauser"];
	$config["basicAuthPass"] = @$_GET["bapass"];
	define("DBPATH", "../db/main.db");
	define("PDO_DSN", "sqlite:".DBPATH);
	
	//make requests against the rest API with the args (get) and postargs provided
	function hitAPI($action, $args, $postargs = null, $cookiefile = null){
		global $config;
		
		$ch = curl_init();
		
		$argStr = "";
		foreach($args as $key => $val){
			$argStr .= "&". $key . "=" . urlencode($val);
		}
		

		$url = $_SERVER["SCRIPT_URI"] . "../rest.php?action=" . $action . 
			"&apiver=" . $config["testAPIVer"] . "&apikey=" . $config["testAPIKey"] . $argStr;
		testLog("hitting '$url' with cookiefile $cookiefile");

		curl_setopt($ch, CURLOPT_URL, $url);
		//curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	
		if($cookiefile !==null){
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile);
		}
		if($postargs !==null){
			testLog("Using post args: " . var_export($postargs,true));
			curl_setopt($ch, CURLOPT_POST, $cookiefile);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postargs);			
		}
		if($config["basicAuth"]){
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_USERPWD, $config["basicAuthUser"].":".$config["basicAuthPass"]);
		}

		$res = curl_exec($ch);		
		$reqInfo = curl_getinfo($ch);
		
		//separate out headers
		/*$reqStr = preg_split("/\n\s*\n/", $res);
		foreach($reqStr as $r){
			//ignore HTTP 100
			if(preg_match("/^HTTP\/1\.1 100 Continue/",$r))
				continue;
			
		}*/
		
		curl_close($ch);
		
		return array("info" => $reqInfo, "body" => $res);

}
	
	/*
	* Perform a full test
	* $tests is an array of return attributes to test against in the form
	* $tests => [
			headers => [
				[ headerName => regex ],
				...
			],
			statusCodes => [
				pass => regex,
				fail => regex				
			],
			contentType => regex,
			body	=> regex,
			json	=> TBD
				
	]
	
	You can include/omit any of the above tests 
	*/
	function performTest($action, $getArgs, $postArgs, $checks = array(), $doLogin = true){
		global $config;
		
		$cookiefile = null;
		if($doLogin){
			$cookiefile = tempnam("/tmp/","toboggan-testing");
			
			//login first
			hitAPI("login", array(),array("username" => $config["testUser"], "password" => $config["testPass"]), $cookiefile);
		}		
		
		$res = hitAPI($action, $getArgs, $postArgs, $cookiefile);
		
		testLog($res["info"]);

		//do tests
		$results = array(
			"action" => $action,
			"url" => $res["info"]["url"],
			"getArgs" => $getArgs,
			"doLogin" => $doLogin,
			"fullResponse" => $res["body"],
		);


		//status code checks
		if(isset($checks["statusCodes"])){
			$doPassCheck = isset($checks["statusCodes"]["pass"]);
			$doFailCheck = isset($checks["statusCodes"]["fail"]);
			$testPassed = false;
			//if we only have a "fail" check, default to pass
			if(!$doPassCheck && $doFailCheck){
				$testPassed = true;
			}			

			if($doPassCheck){
				$testPassed = preg_match("/".$checks["statusCodes"]["pass"]."/", $res["info"]["http_code"]) !== 0; // pass if a pass code matched
			}
			//fail has precendece
			if($doFailCheck){
				if(preg_match("/".$checks["statusCodes"]["fail"]."/", $res["info"]["http_code"]) !== 0){
					$testPassed = false; // fail only if a fail code matched
				}
			}
			$results["checks"]["statusCodes"]["passed"] = $testPassed;
			$results["checks"]["statusCodes"]["result"] = $res["info"]["http_code"];
			$results["checks"]["statusCodes"]["checks"] = $checks["statusCodes"];
		}

		//do json checks
		if(isset($checks["json"])){

			//HACKS! - because of arg validator call back
			global $jsonFailReason, $jsonCheckPassed;		
			$jsonCheckPassed = true;
			$jsonFailReason = "Test Passed";

			$av = new ArgValidator("jsonValidationError");
			$jsonArr = json_decode($res["body"],true);
//			testLog($res["body"]);
//			testLog(json_decode($res["body"]), true);
			
			if( $jsonArr == null ){
				$jsonCheckPassed = false;
				$jsonFailReason = "Invalid json";
			} else{
				$av->validateArgs($jsonArr,$checks["json"]);
			}
		
			$results["checks"]["json"]["passed"] = $jsonCheckPassed;
			$results["checks"]["json"]["result"] = $jsonFailReason;
			$results["checks"]["json"]["checks"] = $checks["json"];
		}//End of JSON checks
		
		//do SQL checks
		if(isset($checks["sql"])){
				
				$conn = getDBConnection();
				$stmt = $conn->prepare($checks["sql"]["query"]);
				$stmt->execute();

				$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$conn = null;
				
				/*$rows = array(
					"test1" => "val1",
					"test2" => array(
						"subkey1" => "subval1",
					),
				);
				$checks["sql"]["validate"] = array(
					"test1" => "val1",
					"test2" => array(
						"subkey2" => "subval1",
					),
				);*/
						
			
				if(!compareArrays($checks["sql"]["validate"], $rows) || !compareArrays($rows,$checks["sql"]["validate"])){
					$sqlCheckPassed = false;
					$sqlCheckResult = "Arrays are different";
				} else {
					$sqlCheckPassed = true;
					$sqlCheckResult = "OK";
				}
					
				
				//var_dump($rows);
				//var_dump($checks["sql"]["validate"]);
				
				$results["checks"]["sql"]["passed"] = $sqlCheckPassed;
				$results["checks"]["sql"]["result"] = $sqlCheckResult;
				$results["checks"]["sql"]["checks"] = $checks["sql"];
				
		}//End of SQL checks
		
		//clean up
		if($cookiefile !==null){
			unlink($cookiefile);
		}

		return $results;
//		$t = new TBTestStatusCode($res);

	}

	function testLog($text){
		if(isset($_GET["debug"])){
			echo "<pre>";
			var_dump($text);
			echo "</pre>";
		}
	}

	function jsonValidationError($msg, $argName = "", $argVal = ""){
		global $jsonFailReason, $jsonCheckPassed;
		$jsonFailReason = "$msg ($argName = $argVal)";
		$jsonCheckPassed = false;
		//testLog("Validation Error - $msg ($argName = $argVal)");
	}
	
	//recursive function to validate one array against another in both structure and values
	function compareArrays($arr1, $arr2){
		$fail = false;
		foreach ($arr1 as $key1 => $val1){
			if(!isset($arr2[$key1]) && @$arr2[$key1] !== null){ // if key is not present in the second array
				$fail = true; 
				//var_dump("compareArrays fail - key not present in second array:". $key1);
				break;
			} else if (is_array($val1) && is_array($arr2[$key1])){ //if both arrays
				//var_dump("recursing into $key1");
				$res = compareArrays($val1, $arr2[$key1]);
				//var_dump("stepping out back to  $key1");
				if(!$res){				
					$fail = true;
					var_dump("compareArrays fail - not both arrays:". $key1);
					break;
				}
				
			} else if((string)$val1 !== (string)$arr2[$key1]){ //if values are different
				$fail = true; 
				//var_dump("compareArrays fail - values differ for key:". $key1);
				break;
			}
		}
		
		return !$fail;
	}

	//hacky! all this should be OOPed but feck it for now
	$tests = array();
	$results = array();
	//registers a test to be run in the batch
	function registerTest($action, $getArgs, $checks, $login = true, $postArgs = null){
		global $tests;
		$tests[] = array("action" => $action, "getArgs" => $getArgs, "postArgs" => $postArgs, "checks" =>  $checks, "login" => $login);
	}

	//testsToRun is an array of names of tests to run, if null all are run
	function runTests($actionsToTest = null){
		global $tests, $results;		
		
		foreach($tests as $test){			
			if($actionsToTest === null || in_array($test["action"], $actionsToTest)){ // only run tests we asked for
				$results[] = performTest($test["action"], $test["getArgs"], $test["postArgs"], $test["checks"], $test["login"]);
				testLog($results[count($results)- 1]);
			}
		}
	}

	function displayResultsTable(){
		global $results;

		//totals
		$numPassed = 0;
		$numFailed = 0;
		$numChecks = 0;
		foreach($results as $result){
			foreach($result["checks"] as $name => $res){
				$numChecks++;
				if($res["passed"])
					$numPassed++;
				else
					$numFailed++;
			}
		}
		//print totals
		echo "<table><tr><th colspan='2'>Totals</th></tr>";
		echo "<tr><td>Total # of checks</td><td>$numChecks</td></tr>";
		echo "<tr><td>Total passed</td><td class='".($numPassed == $numChecks?"passed":"failed")."'>$numPassed</td></tr>";
		echo "<tr><td>Total failed</td><td class='".(!$numFailed?"passed":"failed")."'>$numFailed</td></tr>";
		echo "</table><br /><br />"; //HACKES!

		//main results table
		echo "<table>";
		echo "<th>Test action</th><th>Results</th>";
		foreach($results as $result){
			echo "<tr>";
			echo "<td style='width:150px'>";
				echo $result["action"] . ($result["doLogin"]?" (+login)":"(no login)");
				echo "<br /><span  onclick='$(this).children().toggle();return false;'>Show URL ";
				echo "<a href='".$result["url"]."' style='display:none'>".$result["url"]."</a></span>";
			echo "</td>";
			echo "<td style='width100%'>";
			echo "<table class='innerTable'>";	
				echo "<th>Check</th><th>Passed?</th><th>Result</th>";
				foreach($result["checks"] as $name => $res){
					echo "<tr class='".($res["passed"]?"passed":"failed")."'>";
					echo "<td>".$name."</td>";
					echo "<td>".($res["passed"]?"Passed":"Failed")."</td>";
					echo "<td>".$res["result"]."</td>";
					//testLog($res);	
					echo "<td class='fullChecks' style='display:none' colspan='4'><pre>".htmlentities(var_export($res["checks"],true))."</pre></td>";
					echo "</tr>";
				}
			echo "</table>";
			echo "</td></tr>";
			echo "<tr class='fullResponse' style='display:none'><td colspan='4'><pre>".htmlentities($result["fullResponse"])."</pre></td></tr>";
		}
		echo "</table>";
	}
	
/**
* get a database connection
*/
function getDBConnection()
{
	if(!is_readable(DBPATH)){
		testLog("DB does not exist or is not readable. If this is a new installation did you run install.sh? (See README)", 500); 
	}	
	$db = new PDO(PDO_DSN);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	//enable foreign key enforcement - not enabled by default in sqlite and must be done per connection
	$db->exec("PRAGMA foreign_keys = ON");	
	
	return $db;		
}

?> 
