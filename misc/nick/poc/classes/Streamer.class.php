<?php

class Streamer
{
	public $id, $fromExt, $toExt, $cmd, $mime, $outputMediaType, $bitrateCmd;
	
	function __construct($aid, $afromExt, $atoExt, $acmd, $amime, $aoutputMediaType, $abitrateCmd = null)
	{
		$this->id 				= $aid;
		$this->fromExt 			= $afromExt;
		$this->toExt 			= $atoExt;
		$this->cmd				= $acmd;
		$this->mime				= $amime;
		$this->outputMediaType 	= $aoutputMediaType;
		$this->bitrateCmd		= $abitrateCmd;
	}
	
}


?>