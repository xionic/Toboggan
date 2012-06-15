<?php
/**
* Class to validate arguments to functions
*/
class ArgValidator{
	
	public $argArray, $argDesc, $errCallback;
	
	private $version = "PHPArgValidator Version 0.9";
	
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
	* func			- 	provided Closure must return true. 
	* lbound arg	-	must not be below arg (e.g. "lbound 2")
	* ubound arg	- 	must not be above arg (e.g. "ubound 600")
	* regex arg		- 	must match regex given be arg
	*/
	public function validateArgs($argArray, $argDesc)
	{
		$this->argArray = $argArray;
		$this->argDesc = $argDesc;
		
		//validate our own args
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
		foreach($this->argDesc as $arg => $constraintArr)
		{	
			if(!is_array($constraintArr)) // ensure constraints are provided as an array
			{
				throw new Exception("Constraints must be an array");
				return false;
			}
			//preprocess constraints to trim and apply optional constraint
			$constraints = array();
			foreach($constraintArr as $tc)
			{	
				$newtc = null;
				if($tc instanceof Closure ) // don't try to trim closures
				{
					$newtc = $tc;
				}
				else{
					$temptc = explode(" ", $tc, 2); //split out constraint and arguments to constaint if applicable e.g. "lbound 1"
					
					$newtc["constraint"] = trim($temptc[0]);	
					if(count($temptc) > 1) // if constaint has an argument
					{
						$newtc["constraintArg"] = $temptc[1];	
					}
					
					if($newtc["constraint"] == "optional" && !isset($this->argArray[$arg])) // check for optional arg
					{
						continue 2; // ignore constraints if optional arg is present
					}
				}
				$constraints[] = $newtc;

			}
		
			if(!isset($this->argArray[$arg]))
			{
				call_user_func($this->errCallback,"Missing argument: ". $arg, $arg);
				continue;
			}
			//get the current arg value
			$curValue = $this->argArray[$arg];
			
			foreach($constraints as $c)
			{	
				//apply contraints which cannot be done using a switch
				if($c instanceof Closure)
				{
					$this->checkUserFunc($c,$curValue,$arg);				
				}
				else
				{
				
					//apply the constraints
					switch($c["constraint"])
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
							
						case "lbound":
							$this->checkLbound($c["constraintArg"],$curValue,$arg);
							break;
						
						case "ubound":
							$this->checkUbound($c["constraintArg"],$curValue,$arg);
							break;
						
						case "regex":
							$this->checkRegex($c["constraintArg"],$curValue,$arg);
							break;
							
						case "optional"; // handled above - needed here to prevent exception
							break;
							
						default:
							throw new Exception("Constraint ". htmlentities($c["constraint"]) . " is unsupported");
							break;
						
					}
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
			return false;
		}
		return true;
	}
	
	private function checkUserFunc($func, $value, $arg)
	{
		if(call_user_func($func,$value) !== true)
		{
			call_user_func($this->errCallback,"Argument failed user function validation: ". $arg, $arg, $value);
			return false;
		}
		return true;
	}
	
	private function checkLbound($lbound, $value, $arg)
	{
		$lbound = (int) $lbound;
		if(!is_numeric($lbound))
		{
			call_user_func($this->errCallback,"Argument to lbound must be numeric: ". $arg, $arg, $value);
			return false;
		}
		else{
			if(!$this->checkIsNumeric($value, $arg) || $value < $lbound)
		{
			call_user_func($this->errCallback,"Argument is below lbound(".$lbound."): ". $arg, $arg, $value);
			return false;
		}
		return true;
		}
	}
	private function checkUbound($ubound, $value, $arg)
	{
		$ubound= (int) $ubound;
		if(!is_numeric($ubound))
		{
			call_user_func($this->errCallback,"Argument to ubound must be numeric: ". $arg, $arg, $value);
			return false;
		}
		else{
			if(!$this->checkIsNumeric($value, $arg) || $value > $ubound)
		{
			call_user_func($this->errCallback,"Argument is below ubound(".$ubound."): ". $arg, $arg, $value);
			return false;
		}
		return true;
		}
	}
	
	private function checkRegex($regex, $value, $arg)
	{
		if((preg_match($regex,$value)) !== 1)
		{
			call_user_func($this->errCallback,"Argument is does not match regex(".$regex."): ". $arg, $arg, $value);
			return false;
		}
		return true;
	}
	
	public function getVersion()
	{
		return $this->version;
	}
}



?>