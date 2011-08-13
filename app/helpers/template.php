<?php

//===============================================================
// Template
//===============================================================
class Template extends KISS_View {

	//Example of overriding a constructor/method, add some code then pass control back to parent
	function __construct($vars='') {
		$this->vars = $vars;
		$file = $this->getTemplate();
		parent::__construct($file,$vars);
	}

	function output($vars=''){
		$template = new Template($vars);
		$GLOBALS['body'] = $template->vars["body"];
		$GLOBALS['head'] = $template->get("head");
		$GLOBALS['foot'] = $template->get("foot");
		return parent::do_dump($template->file, $template->vars);
	}
	
	function head( $vars=false ){
		$data = $GLOBALS['head'];
		foreach($data as $name=>$html){
			echo "$html\n";
		}		
	}
	
	function body($view=false){
		$data = $GLOBALS['body'];
		foreach($data as $part){ 
			if ( $view && !isset($part['status']) )
			  View::do_dump( getPath('views/main/body-'. $view .'.php'), $part);
			elseif ($part['view'])
			  View::do_dump( $part['view'], $part);
			else
			  View::do_dump( getPath('views/main/body.php'), $part);
		}
	}
	
	function foot($vars=false){
		$data = $GLOBALS['foot'];
		foreach($data as $name=>$html){
			echo "$html\n";
		}
	}
	/*
	function display($data='', $names=''){
		if (is_array($data))
		  foreach($data as $name=>$html)
		  	if ( ($names == '' ) || (!is_array($names) && $names == $name ) || (is_array($names) && array_key_exists($name, $names)) )
			  echo "$html\n";
		elseif ( is_array($names) )
		  foreach($names as $name)
		  	if ( array_key_exists($name, $block) )
			  echo "$html\n";
	}
	*/
	function get($name=''){
		$data = array();
		$files = findFiles( $name.'.php' );
		
		foreach($files as $view){
			 $section = $this->getSection( $view );
			 $data[$section] = View::do_fetch( $view, $this->vars);
		}
		return $data;
	}
	
	function getTemplate(){
		// support for mobile template
		if(array_key_exists('IS_MOBILE', $GLOBALS) && $GLOBALS['IS_MOBILE'] == true && is_file(TEMPLATES."mobile.php") ){ 
			$file = TEMPLATES."mobile.php";
		} else {
			$file = (array_key_exists('template', $this->vars) && is_file(TEMPLATES.$this->vars['template'])) ? TEMPLATES.$this->vars['template'] : TEMPLATES.DEFAULT_TEMPLATE;
		}
		return $file;
	}
	
	// find the section a view file belongs to
	function getSection( $file ){
		// first check if it is in a plugins folder
		if( preg_match('/[a-z0-9_.\/\\\]plugins[a-z0-9_.\/\\\]*$/i', $file, $match) ) {
			// $match =  /plugins/{section}/views/file.php
			$path = explode("/", $match[0]);
			// the lovation of the section is rather hardcoded here but there must be a better way...
			$section = $path[2];
		//otherwise it is in the main BASE or APP view folder
		} else {
			$path = preg_split('/views/', $file);
			if( count($path) > 1 ){
			$path = explode("/", $path[1]);
			$section = $path[1];
			}
		}
		// ultimate fallback
		if(!isset($section)){ $section = "none"; }
		return $section;
	}
	
	
	function doList( $selected=null){
		
		$data['template']['selected'] = $selected;
		
		if ($handle = opendir(TEMPLATES)) {
			while (false !== ($template = readdir($handle))) {
				if ($template == '.' || $template == '..') { 
				  continue; 
				} 
				if ( is_file(TEMPLATES.$template) ) {
					$data['template']['list'][] = array( 	'value' => $template, 
															'title' => beautify($template)
														);
				}
			}	
			View::do_dump( getPath('views/admin/list_templates.php'), $data);
		} else {
			return false;
		}			
	}
}

?>