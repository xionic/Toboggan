<?php

class FileOps{
	public static function filesize($filepath){
		if(!FileOps::isOverPHP_MAX_INT($filepath)){ // under max size - php func OK
			return filesize($filepath);
		}
		else{ // too large - need to manually calc

			//check we have BCMATH
			if(!in_array('bcmath',get_loaded_extensions()))
				return false;

			$chunk = PHP_INT_MAX;
			$length = "0";
			$fh = fopen($filepath,'rb');
			while(true){
				fseek($fh, $chunk, SEEK_CUR);
				if(FileOps::isBeyondEOF($fh)){
					if($chunk == 1) break;
					//rewind and reduce chunk
					fseek($fh,-1*$chunk, SEEK_CUR);
					$chunk = (int)($chunk/2);
				}else{
					$length = bcadd($length,$chunk);
				}
			}
			fclose($fh);
			return bcadd($length,1); // last byte not added in loop
		}
	}

	private static function isOverPHP_MAX_INT($filepath){
		$fh = fopen($filepath, 'rb');

		fseek($fh, PHP_INT_MAX, SEEK_CUR);
		$result = !FileOps::isBeyondEOF($fh);
		fclose($fh);
		return $result;
	}

	private static function isBeyondEOF($fh){
		$result = (false === fgetc($fh));
		if(!$result)
			fseek($fh,-1,SEEK_CUR); // return pointer to pos before fgetc

		return $result;
	}
}

?>
