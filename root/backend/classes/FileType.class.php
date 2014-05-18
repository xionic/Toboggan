<?php

class FileType
{
	public $id, $extension, $mimeType, $mediaType, $bitrateCmdID, $durationCmdID;
	
	//construct a FileType object from an extension by pulling the details from the DB
	function __construct($id)
	{
		$conn = getDBConnection();
	
		$stmt = $conn->prepare("
			SELECT
				idfileType,
				extension,
				mimeType,
				mediaType,
				idbitrateCmd,
				iddurationCmd
			FROM 
				FileType
			WHERE 
				idfileType = :idfileType"
		);
		$stmt->bindValue(":idfileType",$id, PDO::PARAM_INT);
		$stmt->execute();
		
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		closeDBConnection($conn);	

		if(count($results) == 0)
		{
				throw new NoSuchFileTypeException("No FileType with id: " . $id);
				return null;
		}
			
		$row = $results[0];
		
		$this->id = $row["idfileType"];	
		$this->extension = $row["extension"];
		$this->mimeType = $row["mimeType"];
		$this->mediaType = $row["mediaType"];
		$this->bitrateCmdID = $row["idbitrateCmd"];
		$this->durationCmdID = $row["iddurationCmd"];	
	}
	
	//return a file type object constructed from an extension (which are unique) - no function overloading :(
	public static function getFileTypeFromExtension($ext){
		//db connection
		$conn = getDBConnection();
			
		//get all settings for each streamer apart from fromExt.Extension and aggregate rows which are identical (DISTINCT)
		$stmt = $conn->prepare("
			SELECT 
				idfileType			
			FROM 
				FileType
			WHERE
				extension = :extension
		");
		$stmt->bindValue(":extension",$ext, PDO::PARAM_STR);
		$stmt->execute();			
		
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);		
		closeDBConnection($conn);
		
		if($rows == false || count($rows) == 0)
		{
				throw new NoSuchFileTypeException("No FileType with extension: " . $ext);
				return null;
		}			
		return new FileType($rows[0]["idfileType"]);
	}
	
	//return the converter objects which are applicable to this FileType
	function getAvailableConverters(){		
		$conn = getDBConnection();
		
		$stmt = $conn->prepare("
			SELECT 
				idfileConverter				
			FROM 
				FileConverter
			WHERE 
				fromidfileType = :fromidfileType"
		);
		$stmt->bindValue(":fromidfileType",$this->id, PDO::PARAM_INT);
		$stmt->execute();
		
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		closeDBConnection($conn);	
		
		$suitableStreamers = array();
		foreach($results as $row){
			//check that the user has permission
			if(checkUserPermission("accessStreamer", $row["idfileConverter"])){
				$suitableStreamers[] = new FileConverter($row["idfileConverter"]);
			}
		}
		return $suitableStreamers;
	}
	
	//query the db to get the duration command from the id
	function getDurationCommand(){	
		if($this->durationCmdID == null){
			return null; // we don't have a command for this
		}
	
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

		return $results[0]["command"];		
	}
	
	//query the db to get the bitrate command from the id
	function getBitrateCommand(){
		if($this->bitrateCmdID == null){
			return null; // we don't have a command for this
		}
	
		$conn = getDBConnection();
	
		$stmt = $conn->prepare("
			SELECT
				command
			FROM 
				Command c1 
			WHERE 
				idcommand = :idcommand"
		);
		$stmt->bindValue(":idcommand",$this->bitrateCmdID, PDO::PARAM_INT);
		$stmt->execute();
		
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		closeDBConnection($conn);	

		return $results[0]["command"];		
	}
	
}


?>
