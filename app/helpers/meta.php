<?php

class Meta {
	
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
		echo $GLOBALS['config']['main']['site_name'];
	}
	
	function getDescription(){
		echo $GLOBALS['config']['main']['site_description'];
	}
}

?>