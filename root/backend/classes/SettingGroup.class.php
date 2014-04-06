<?php

/*
* Represents a group of Settings data, with a schema object to describe the structure
*/
class SettingGroup {

	private $data, $schema;

	public function setData($sd){
		$this->data = $sd;
	}
	public function setSchema($ss){
		$this->schema = $ss;
	}
	
	public function getSettingsObject(){
		return array("schema" => $this->schema, "data" => $this->data);
	}
	
}

?>