<?php

class Meta {
	
	public static function display($type){
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
	
	public static function title(){
		echo ( array_key_exists('meta', $GLOBALS) && $GLOBALS['meta']['title'] ) ? $GLOBALS['meta']['title'] : $GLOBALS['config']['main']['site_name'];
	}
	
	public static function description(){
		echo (  array_key_exists('meta', $GLOBALS) && $GLOBALS['meta']['description'] ) ? $GLOBALS['meta']['description'] : $GLOBALS['config']['main']['site_description'];
	}
	
	public static function url($query=false){
		if(  array_key_exists('meta', $GLOBALS) && $GLOBALS['meta']['url'] ) {
			echo $GLOBALS['meta']['url'];
		} else {
			$url = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
			
			if( $query ){ 
				echo $url;
			} else {
				echo "http://" . $_SERVER['SERVER_NAME'] . parse_url($url, PHP_URL_PATH);
			}
		}
	}
}

?>