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
		// compile the page with the existing data
		$output = parent::do_fetch($template->file, $template->vars);
		// post-process
		// - check if any minification is necessary
		$output = $template->minify($output);
		// output the final markup - clear whitespace (if not in debug mode)
		echo $output;
		
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
	
	function minify( $html ){
		
		$dom = new DOMDocument;
		$dom->preserveWhiteSpace = false; 
		@$dom->loadHTML($html);
 		
		$scripts = $dom->getElementsByTagName('script');
 		
		$minify = array();
		$delete = array(); 
		
		// find all the scripts that need to be minified
		foreach ($scripts as $script){
			$type = $script->getAttribute('data-type');
			
			if( strpos($type, "google-closure") ){
				// capture attributes
				$group = $script->getAttribute('data-group');
				$order = (int) $script->getAttribute('data-order');
				$encode = $script->getAttribute('data-encode');
				$src = $script->getAttribute('src');
				// order the scripts (if available)
				if( $order ) {
					$minify[$group][$order] = array( "src" => $src, "encode" => $encode);
				} else { 
					$minify[$group][] = array( "src" => $src, "encode" => $encode);
				}
				// queue to delete
				$delete[] = $script; 
							
			}

		}
		
		// FIX: stop if the are no libraries to minify
		if( !count($minify)) return $dom->saveHTML();
		
		// remove the scripts from the dom
		foreach( $delete as $tag ){ 
		  $tag->parentNode->removeChild($tag); 
		} 
		
		// sort results
		ksort_recursive( $minify );
		
		// FIX: create the dir if not available
		if( !is_dir( APP. "public/assets/js/" ) ) mkdir(APP. "public/assets/js/", 0775, true);
		
		// call google-closure
		foreach( $minify as $name=>$group ){
			$min = new Minify();
			// get th eencoding from the first member of the group
			$first = current($group);
			$encode = $first["encode"];
			// loop through the group and add the files
			foreach( $group as $script ){
				$file = ( !strpos($script["src"], "http") ) ? $_SERVER['DOCUMENT_ROOT'] . $script["src"] : $script["src"];
				$min->add( $file );
			}
			
			$min->cacheDir( APP. "public/assets/js/" )
   				->setFile( $name.".min" );
				
			if( !DEBUG ){ 
				$min->quiet()
   					->hideDebugInfo();
			}
   			
			// condition the method of minification here...
			switch( $encode ){ 
				case "whitespace": 
					$min->whitespaceOnly();
				break;
				case "simple": 
					$min->simpleMode();
				break;
				case "advanced": 
					$min->advancedMode();
				break;
				default: 
					$min->simpleMode();
				break;
			
			}
			
			//->useClosureLibrary()
   			$min->create();
			
			// create the script reference
			//var_dump( "/assets/js/" . $name.".min" );
		
		}
		
		// Unfortunately this messes up javascript templates
		//$output =  $dom->saveHTML();
		$output =  $html;
		// Legacy regular expression to match minified scripts
		$output = preg_replace("/<script (.)*(google-closure)+(.)*>(.)*?<\/script>/", "", $output );
		// TEMP: for now replacingcomments with script tags (use require.js in the future)
		$output = preg_replace("/<!-- min: (\w+) -->/i", '<script type="text/javascript" src="'. myCDN() .'/assets/js/${1}.min.js"></script>', $output);
		
		// if in debug mode return the original html
		//return ( DEBUG ) ? $html : $output;
		// output the final markup - clear whitespace (if not in debug mode)
		return ( DEBUG ) ? $output : $this->trimWhitespace( $output );
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
	
	function trimWhitespace( $string ){
		// replace multiple spaces with one
		return preg_replace( '/\s+/', ' ', $string );
	}
  
}

?>