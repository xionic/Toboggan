<?php
/**
	Performs pre-parsing on CSS files

	Adds support for // comments and $variables

	Variables must be defined in a comment at the start
	of the file like: $variable=value; with one per line

*/

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

header("Content-Type: text/css");
$filename = $_SERVER['QUERY_STRING'];

if ( ! preg_match("/^[a-zA-Z0-9_-]+\.css$/", $filename) || ! file_exists($filename) )
{
	header("HTTP/1.0 404 Not Found");
	exit();
}

$inBulkComment=false;
$fh = fopen($filename, "r");
$userVars = array();
while (!feof($fh)) {
	$buffer = rtrim(fgets($fh), "\n\r");

	if(strpos($buffer,'/*')!==false)
		$inBulkComment=true;
	if( ($l = strpos($buffer,'*/')) !== false && ($l==0 || ($l>0 && substr($buffer, $l-1, 1) !== '/')) )
		$inBulkComment=false;

	//support converting "//" comments into /* */
	// but only if we've not inside a /* */ block
	if($inBulkComment===false && strpos($buffer,"*/")===false ||
		strpos($buffer, "*/") < strpos($buffer,"//") )
			$buffer = preg_replace("/(^|[^:]+)\/\/(.*)$/", "$1/*$2*/", $buffer);

	/**
		Supporting variables
	*/
	//TODO:: support a vars_definition block to enable the following regex:
	//setting variables:
	$match_count = preg_match("/^\s*\\$([a-zA-Z0-9_-]+)\s*=\s*([^;]+);$/", $buffer, $matches);
	if($match_count)
	{
		$userVars[$matches[1]]=$matches[2];
	}

	//do variable replacement
	// if the line contains a dollar
	if ( !$match_count && strstr($buffer,'$') !== FALSE )
	{
		foreach($userVars as $varName => $varValue)
		{
			$buffer = preg_replace("/(^|[^\\\\])\\$".$varName."/", "\${1}${varValue}", $buffer);
		}
	}
	echo "${buffer}\n";
}
fclose($fh);

?>
