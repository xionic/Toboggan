<?php

//	require_once("TBTest.class.php");
//	require_once("TBTestStatusCode.class.php");
	require_once("../lib/PHPArgValidator/PHPArgValidator.class.php");

	$config["testAPIKey"] = "{05C8236E-4CB2-11E1-9AD8-A28BA559B8BC}";
	$config["testAPIVer"] = "0.58";
	$config["testUser"] = "autotest";
	$config["testPass"] =  base64_encode(hash("sha256","password", true));
	$config["basicAuth"] = isset($_GET["bauser"]);
	$config["basicAuthUser"] = $_GET["bauser"];
	$config["basicAuthPass"] = $_GET["bapass"];
	
	//make requests agains the rest API with the args (get) and postargs provided
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
		if($cookiefile !==null){
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile);
		}
		if($postargs !==null){
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
	function performTest($action, $getArgs, $checks = array(), $doLogin = true){
		global $config;
		
		$cookiefile = null;
		if($doLogin){
			$cookiefile = tempnam("/tmp/","toboggan-testing");
			
			//login first
			hitAPI("login", array(),array("username" => $config["testUser"], "password" => $config["testPass"]), $cookiefile);
		}		
		
		$res = hitAPI($action, $getArgs, null, $cookiefile);
		
		//testLog($res["info"]);

		//do tests
		$results = array(
			"action" => $action,
			"getArgs" => $getArgs,
			"doLogin" => $doLogin,
			"fullResponse" => $res["body"]
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
		}

		//End of JSON checks
		
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
			var_dump($text );
			echo "</pre>";
		}
	}

	function jsonValidationError($msg, $argName = "", $argVal = ""){
		global $jsonFailReason, $jsonCheckPassed;
		$jsonFailReason = "$msg ($argName = $argVal)";
		$jsonCheckPassed = false;
		//testLog("Validation Error - $msg ($argName = $argVal)");
	}

	//hacky! all this should be OOPed but feck it for now
	$tests = array();
	$results = array();
	//registers a test to be run in the batch
	function registerTest($action, $getArgs, $checks, $login = true){
		global $tests;
		$tests[] = array("action" => $action, "getArgs" => $getArgs, "checks" =>  $checks, "login" => $login);
	}

	function runTests(){
		global $tests, $results;
		foreach($tests as $test){
			$results[] = performTest($test["action"], $test["getArgs"], $test["checks"], $test["login"]);
			testLog($results[count($results)- 1]);
		}
	}

	function displayResultsTable(){
		global $results;
		echo "<table>";
		echo "<th>Test action</th><th>Results</th>";
		foreach($results as $result){
			echo "<tr>";
			echo "<td style='width:150px'>";
				echo $result["action"];
			echo "</td>";
			echo "<td style='width100%'>";
			echo "<table class='innerTable'>";	
				echo "<th>Check</th><th>Passed?</th><th>Result</th>";
				foreach($result["checks"] as $name => $res){
					echo "<tr class='".($res["passed"]?"passed":"failed")."'>";
					echo "<td>".$name."</td>";
					echo "<td>".($res["passed"]?"Passed":"Failed")."</td>";
					echo "<td>".$res["result"]."</td>";
						
					echo "</tr>";
				}
			echo "</table>";
			echo "</td></tr>";
			echo "<tr class='fullResponse' style='display:none'><td colspan='4'><pre>".htmlentities($result["fullResponse"])."</pre></td></tr>";
		}
		echo "</table>";
	}

?> 
