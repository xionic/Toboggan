<?php

class FileType
{
	public $extension, $mimeType, $mediaType, $bitrateCmd, $durationCmd;
	
	//construct a FileType object from an extension by pulling the details from the DB
	function __construct($extension)
	{
		$conn = getDBConnection();
	
		$stmt = $conn->prepare("
			SELECT
				extension,
				mimeType,
				mediaType,
				c1.command AS bitrateCmd,
				c2.command AS durationCmd
			FROM 
				FileType 
				LEFT JOIN Command c1 ON (c1.idcommand = idbitrateCmd)
				LEFT JOIN Command c2 On (c2.idcommand = iddurationCmd)
			WHERE 
				extension = :extension"
		);
		$stmt->bindValue(":extension",$extension, PDO::PARAM_STR);
		$stmt->execute();
		
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		closeDBConnection($conn);	

		if(count($results) == 0)
		{
				throw new NoSuchFileTypeException("No such extension: " . $extension);
		}
			
		$row = $results[0];
		
		$this->extension = $row["extension"];
		$this->mimeType = $row["mimeType"];
		$this->mediaType = $row["mediaType"];
		$this->bitrateCmd = $row["bitrateCmd"];
		$this->durationCmd = $row["durationCmd"];	
	}
	
	//return the converter objects which are applicable to this FileType
	function getAvailableConverters(){		
		$conn = getDBConnection();
		
		$stmt = $conn->prepare("
			SELECT 
				idfileConverter, 
				fromFileType,
				toFileType,
				command
			FROM 
				FileConverter 
				INNER JOIN Command USING (idcommand)
			WHERE 
				fromFileType = :fromExt"
		);
		$stmt->bindValue(":fromExt",$this->extension, PDO::PARAM_STR);
		$stmt->execute();
		
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		closeDBConnection($conn);	
		
		$suitableStreamers = array();
		foreach($results as $row){
			//check that the user has permission
			if(checkUserPermission("accessStreamer", $row["idfileConverter"])){
				$fc = new FileConverter();
				$fc->id 			= $row["idfileConverter"];
				$fc->fromFileType 	= $this; 
				$fc->toFileType 	= new FileType($row["toFileType"]);
				$fc->cmd 			= $row["command"];
				$suitableStreamers[] = $fc;
			}
		}
		return $suitableStreamers;
	}
	
}


?>