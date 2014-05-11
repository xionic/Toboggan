<?php

class FileConverter
{
	//fromFileType and toFileType are FileType objects 
	public $id, $fromFileType, $toFileType, $cmdID;	
	
	//construct from db id
	public function __construct($id = false)
	{		
		if($id == false)
			return; // no constructor overloading :( - future me - WTF is this for?
			
		$conn = getDBConnection();
		
		$stmt = $conn->prepare("SELECT 
									idfileConverter,
									fromidfileType,
									toidfileType,
									idcommand
								FROM 
									FileConverter 
								WHERE 
									idfileConverter = :idfileConverter");
		$stmt->bindValue(":idfileConverter",$id, PDO::PARAM_INT);
		$stmt->execute();		

		$row = $stmt->fetch(PDO::FETCH_ASSOC);	
		if($row == false || count($row) == 0)
		{
				throw new NoSuchFileConverterException("No such extension: " . $extension);
		}
		
		$stmt->closeCursor();
		closeDBConnection($conn);
		$this->id 				= $row["idfileConverter"];
		$this->fromFileType 	= new FileType($row["fromidfileType"]);
		$this->toFileType 		= new FileType($row["toidfileType"]);
		$this->cmdID			= $row["idcommand"];
		
	}
	
	public function getCommand(){
		$conn = getDBConnection();
	
		$stmt = $conn->prepare("
			SELECT
				command
			FROM 
				Command c1 
			WHERE 
				idcommand = :idcommand"
		);
		$stmt->bindValue(":idcommand",$this->durationCmdID, PDO::PARAM_INT);
		$stmt->execute();
		
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		closeDBConnection($conn);	
			
		return $results[0];	
	}
}


?>