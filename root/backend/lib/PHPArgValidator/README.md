PHPArgValidator
===============

A PHP helper class to validate associative arrays


	*/
	
EXPOSED FUNCTIONS:
===============
validateArgs($arrayOfArgumentsToBeValidated, $arrayOfConstraints)
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
	See example usage below

getVersion
	returns the version number of this clss

EXAMPLE USAGE:
===============

	
/**
* callback function for argument validation - used by ArgValidator class
*/
function handleArgValidationError($msg, $argName="", $argValue="")
{
	echo "<pre>";
	var_dump($msg);
	var_dump($argName);
	var_dump($argValue);
	echo "</pre>";
	exit;
}

$av = new ArgValidator("handleArgValidationError");

$apiargs = $av->validateArgs($_GET, array(
	"test1" => array("string", "notblank"),
	"test2" => array(function($a){return $a > 0;}),
	"test3" => array("lbound 5","ubound 500"),
	"test4" => array("regex /a/"),
	"test5" => array("notzero"),
	"test5" => array("array"),
));
 
