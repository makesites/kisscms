<?php

class Meta {
	
	static function display($type){
		/*
		$meta = new Meta();
		switch( $type ){ 
			case "title": 
				$meta->getTitle();
			case "description": 
				$meta->getDescription();
		}
		*/
	}
	
	function title(){
		echo $GLOBALS['config']['main']['site_name'];
	}
	
	function description(){
		echo $GLOBALS['config']['main']['site_description'];
	}
	
	function url($query=false){
		$url = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		
		if( $query ){ 
			echo $url;
		} else {
			echo "http://" . $_SERVER['SERVER_NAME'] . parse_url($url, PHP_URL_PATH);
		}
	}
}

?>