<?php
require_once("include/functions.php");

/**
* Class to validate arguments to functions
*/
class ArgValidator{
	
	public $argArray, $argDesc, $errCallback;
	
	public function __construct($errCallback){
		// $this->argArray = $argArray;
		// $this->argDesc = $argDesc;
		$this->errCallback = $errCallback;
	}
	/**
	* function to validate GET args for the rest API and return an array of results. it supports validation constraints for each argument.
	* Supported Constraints:
	* int			-	must be an integer (implies 'numeric')
	* numeric		-	must be numeric
	* notzero		-	must not be zero (implies 'numeric')
	* notblank		-	string must not be blank (implies 'string')
	* string		-	must be string
	* array			- 	must be array
	*/
	public function validateArgs($argArray, $argDesc)
	{
		$this->argArray = $argArray;
		$this->argDesc = $argDesc;
		if(!is_array($this->argDesc))
		{
			throw new Exception("Arguments Description must be an array");
			return false;
		}
		elseif(!is_array($this->argArray))
		{
			throw new Exception("Arguments must be an array");
			return false;
		}
		
		//array to be returned
		$retArr = array();
		
		//loop through each argument to be validated
		foreach($this->argDesc as $arg => $constraintString)
		{	
			//get the constraints for the current arg
			$tempconstraints = explode(",", $constraintString);

			//preprocess constraints to trim and apply optional constraint
			$constraints = array();
			foreach($tempconstraints as $tc)
			{
				$newtc = trim($tc);				
				if($newtc == "optional" && !isset($this->argArray[$arg])) // check for optional arg
				{
					continue 2; // ignore constraints if optional arg is not present
				}
				$constraints[] = $newtc;

			}
		
			if(!isset($this->argArray[$arg]))
			{
				call_user_func($this->errCallback,"Missing argument: ". $arg, $arg);
				return false;
			}
			//get the current arg value
			$curValue = $this->argArray[$arg];
			
			
			foreach($constraints as $c)
			{
				//apply the constraints
				switch($c)
				{	
					case "string": 
						$this->checkIsString($curValue, $arg);
						break;
						
					case "numeric":
						$this->checkIsNumeric($curValue, $arg);
						break;	
						
					case "int" :
						$this->checkIsInt($curValue, $arg);
						break;
						
					case "notzero" :
						$this->checkNotZero($curValue, $arg);
						break;
						
					case "notblank" :
						$this->checkNotBlank($curValue, $arg);
						break;
					case "array":
						$this->checkIsArray($curValue, $arg);
						break;
					case "optional"; // handled above - needed here to prevent exception
						break;
						
					default:
						throw new Exception("Constraint ". htmlentities($c) . " is unsupported");
						break;
					
				}
			}
			
			$retArr[$arg] = $curValue;
		}
		return $retArr;
	}	
	
	private function checkIsInt($value, $arg)
	{
		if(!$this->checkIsNumeric($value, $arg) || !is_int($value+0))
		{
			call_user_func($this->errCallback,"Argument is not an integer: ". $arg, $arg, $value);
			return false;
		}
		return true;
	}
	private function checkIsNumeric($value, $arg)
	{
		if(!is_numeric($value))
		{
			call_user_func($this->errCallback,"Argument is not numeric: ". $arg, $arg, $value);
			return false;
		}
		return true;
	}
	
	private function checkNotZero($value, $arg)
	{	
		if(!$this->checkIsNumeric($value, $arg) || $value == 0)
		{
			call_user_func($this->errCallback,"Argument is zero: ". $arg, $arg, $value);
			return false;
		}
		return true;
	
	}
	
	private function checkNotBlank($value, $arg)
	{
		if(!$this->checkIsString($value, $arg) || $value == "")
		{
			call_user_func($this->errCallback,"Argument is a blank string: ". $arg, $arg, $value);
			return false;
		}
		return true;
	}
	
	private function checkIsString($value, $arg)
	{
		if(!is_string($value))
		{
			call_user_func($this->errCallback,"Argument is not a string: ". $arg, $arg, $value);
			return false;
		}
		return true;
	}
	
	private function checkIsArray($value, $arg)
	{
		if(!is_array($value))
		{
			call_user_func($this->errCallback,"Argument is not an array: ". $arg, $arg, $value);
		}
	}
}



?>