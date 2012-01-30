<?php
/**
* get a config option
*/
function getConfig($name){
	global $config;
	return $config[$name];
}

/**
* get the current maximum bandwidth that media should be streamed at
*/
function getCurrentMaxBandwidth(){
	return 100;
}
?>