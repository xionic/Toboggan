<?php
/*
	X-US-Authorization: Method Base64(username)."|".sha1PasswordHash
	
*/

require_once("include/functions.php");

class userLogin {

	public static function getUserRow($username){

		$db = new PDO(PDO_DSN);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$stmt = $db->prepare("SELECT * FROM User WHERE username=:username;");
		$stmt->bindValue(":username",$username);
		$stmt->execute();

		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		return(isset($rows[0])?$rows[0]:null);
	}
	
	/**
	* checks if a user is logged in and returns the userid
	*/
	public static function checkLoggedIn()
	{
		//try getting auth from session
		if(isset($_SESSION["userid"])){
			return($_SESSION["userid"]);
		}
		//if no session then look at HTTP header auth
		else{
			$headers = apache_request_headers();
			if(!isset($headers['X-US-Authorization']))
			{
				return false;
			}
			list($method, $authData) = explode(" ", $headers['X-US-Authorization']);
			
			switch($method)
			{
				case "US-Auth1":
					list($sentUsername, $sentPassHash) = explode("|",$authData);
					$sentUsername = base64_decode($sentUsername);
					
					$rows = userLogin::getUserRow($sentUsername);
					$passhash = $rows['password'];
					
					$ourPassStr = base64_encode(hash("sha256",getConfig("passwordSalt").$sentPassHash, true));
					if($ourPassStr!==$passhash)
					{
						return false;
					}
					
					return $rows['idUser'];
					break;
			}			
		}
		
		//user is not authenticated
		return false;
	}
	/**
	* Alias of checkLoggedIn() - used to return current userid
	*/
	public static function getCurrentUserID()
	{
		return userLogin::checkLoggedIn();
	}
	
	/**
	* check sent login credentials and return user id
	*/
	public static function validate(){
				
		//try POST VAR auth
		if(isset($_POST["username"]) && isset($_POST["password"]))
		{
			$userRows = userLogin::getUserRow($_POST["username"]);
			$passhash = $userRows['password'];
			$ourPassStr = base64_encode(hash("sha256",getConfig("passwordSalt").$_POST["password"], true));
			if($ourPassStr!==$passhash)
			{
				reportError("Authentication failed", 401, "text/plain");
				return false;
			}
			
			//store userid
			$_SESSION["userid"] = $userRows["idUser"];
			
			//return userid
			return $userRows["idUser"];
		}
		//if not session and no POST vars try HTTP header auth
		else{
			$headers = apache_request_headers();
			if(!isset($headers['X-US-Authorization']))
			{
				reportError("Authentication Required", 401, "text/plain");
				return false;
			}
			list($method, $authData) = explode(" ", $headers['X-US-Authorization']);
			
			switch($method)
			{
				case "US-Auth1":
					list($sentUsername, $sentPassHash) = explode("|",$authData);
					$sentUsername = base64_decode($sentUsername);
					
					$rows = userLogin::getUserRow($sentUsername);
					$passhash = $rows['password'];
					
					
					$ourPassStr = base64_encode(hash("sha256",getConfig("passwordSalt").$sentPassHash, true));
					if($ourPassStr!==$passhash)
					{
						reportError("Authentication failed", 401, "text/plain");
						return false;
					}

					return $rows['idUser'];
					break;
			}			
		}
		
		//No auth sent or no existing sessions 
		return false;
		
	}
}