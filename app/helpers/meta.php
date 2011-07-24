<?php

//===============================================================
// Template
//===============================================================
class Meta extends Model {
	
	static function display($type){
		$meta = new Meta();
		switch( $type ){ 
			case "title": 
				$meta->getTitle();
			case "description": 
				$meta->getDescription();
		}
	}
	
	function getTitle(){
		return $GLOBALS['main']['site_name'];
	}
	
	function getDescription(){
		return $GLOBALS['main']['site_description'];
	}
}

?>