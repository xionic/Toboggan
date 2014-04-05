<?php

class FileConverter
{
	//fromFileType and toFileType are FileType objects 
	public $id, $fromFileType, $toFileType, $cmd;	
	
	//construct from db id
	public function __construct($id = false)
	{		
		if($id == false)
			return; // no constructor overloading :(
			
		$conn = getDBConnection();
		
		$stmt = $conn->prepare("SELECT 
									idfileConverter,
									fromFileType,
									toFileType,
									command
								FROM 
									FileConverter 
									INNER JOIN command USING(idcommand)
								WHERE 
									idfileConverter = :idfileConverter");
		$stmt->bindValue(":idfileConverter",$id, PDO::PARAM_INT);
		$stmt->execute();

		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		closeDBConnection($conn);
		
		$this->id 				= $row["idfileConverter"];
		$this->fromFileType 	= new FileType($row["fromFileType"]);
		$this->toFileType 		= new FileType($row["toFileType"]);
		$this->cmd				= $row["command"];
		
	}
	/*
	public function __construct($aid, $afromExt, $atoExt, $acmd)
	{
		$this->id 				= $aid;
		$this->fromFileType 	= $afromExt;
		$this->toFileType 		= $atoExt;
		$this->cmd				= $acmd;
	}*/
}


?>