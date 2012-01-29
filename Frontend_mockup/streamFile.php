<?php
//see: http://www.php-code.net/2010/07/bandiwdth-limit-script/
$bandwidth = '50';	//KB/s

$fn = $_GET['file'];

$fnArray = explode("/",$fn);
$ofnArray = array();

foreach($fnArray as $val)
{
	if($val == "..")
	{
		//delete last location from array
		array_pop($ofnArray);
	}
	else if ($val != ".")
		$ofnArray[] = $val;
}

//construct new path
$newPath = "";
foreach($ofnArray as $dir)
{
	$newPath .= "/".$dir;
}

$newPath = "/media/WD-250_md0/music".$newPath;
var_dump($newPath);
$fh = @fopen($newPath,'rb');
if(!$fh)
{
	echo 'Unable to open file';
	exit;
}

$filename = $ofnArray[count($ofnArray)-1];

$fileSize = filesize($newPath);
header("Content-Type: audio/mpeg");
header("Content-Length: " . $fileSize);
header("Content-disposition: attachment; filename=".$filename);

while(!feof($fh))
{
	print(fread($fh, $bandwidth*1024));
	usleep(1000000);
}

fclose($fh);

?>
