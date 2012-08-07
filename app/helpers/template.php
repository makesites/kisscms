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
		// post-process (in debug don't post process)
		if( !DEBUG ) $output = $template->process($output);
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
	
	
	function process( $html ){
		
		// setup 
		$client = "";
		$group = array();
		$remove = array();
		// make this a config option?
		$baseUrl = "/assets/js/";
		// precaution(s) in case this is the first time we are accessing the client globals (not needed?)
		if( !isset($GLOBALS['client']) ) $GLOBALS['client'] = array();
		if( !isset($GLOBALS['client']['require']) ) $GLOBALS['client']['require'] = array();
		// FIX: create the dir if not available
		if( !is_dir( APP. "public/". $baseUrl ) ) mkdir(APP. "public/". $baseUrl, 0775, true);
		if( !is_dir( APP. "public/js/" ) ) mkdir(APP. "public/js/", 0775, true);
		
		// default require strucure
		$GLOBALS['client']['require'] = array(
			"baseUrl" => $baseUrl, 
			"paths" => array(),
			"shim" => array(),
			"deps" => array()
		);
		
		// map the dom
		$dom = new DOMDocument;
		$dom->preserveWhiteSpace = false; 
		@$dom->loadHTML($html);
 		
		// filter the scripts
		$scripts = $dom->getElementsByTagName('script');
 		
		// check the script attributes
		foreach ($scripts as $script){
			// check out for the supported script attributes
			$data = array();
			$data['path'] = $script->getAttribute('data-path');
			$data['deps'] = $script->getAttribute('data-deps');
			$data['group'] = $script->getAttribute('data-group');
			$data['order'] = (int) $script->getAttribute('data-order');
			$data['encode'] = $script->getAttribute('data-encode');
			$type = $script->getAttribute('type');
			$src = $script->getAttribute('src');
			
			// register types
			$data['minify'] = strpos($type, "google-closure") > 0 || !empty($data['encode']);
			$data['require'] = strpos($type, "require") > 0 || !empty($data['path']);
			
			// leave standard types alone
			if( !$data['minify'] && !$data['require']) continue;
		
			
			// if script processed in any way, remove from the DOM
			$remove[] = $script;
			
			// if no src add to the config file
			if( empty($src) && $data['require'] ) {
				$client .= $script->textContent;
				// no further processing required
				continue;
			}
			
			//get the name from the script src
			$name = substr( str_replace( array($baseUrl, url() ),"", $src), 0, -3);
			
			// there is no grouping if there's no minification :P
			if( $data['minify'] && !empty($data['group']) ) {
				$group[$data['group']][] = array( "src" => $src, "data" => $data );
			} else { 
				// maybe pick the file name as the group name instead...
				$group[$name][] = array( "src" => $src, "data" => $data );
			}
			
			
		}
		
		// process minification
		$this->minify( $group );
		
		// process require configuration
		$dom = $this->config( $group, $dom );
		
		// prepend the client string
		$client = "var KISSCMS = ". json_encode_escaped($GLOBALS['client']) ."; " . $client;
		
		// write config file
		$client_sign = md5($client);
		$file_sign = (is_file(APP. "public/js/client.js")) ? md5_file(APP. "public/js/client.js") : NULL;
		
		$client_src=url("/js/client.js");
		
		// check md5 signature
		if($client_sign == $file_sign){ 
			// do nothing
		} else {
			$write = file_put_contents( APP. "public/js/client.js", $this->trimWhitespace($client) );
			// force the caching to reload the client
			$client_src = $client_src ."?time=". time();
		}
		// in any case include the client script 
		// get the main require js
		$main = $dom->getElementById("require-main");
		// render a standard script tag
		$script = $dom->createElement('script');
		$script->setAttribute("type", "text/javascript");
		$script->setAttribute("src", $client_src);
		$script->setAttribute("defer", "defer");
		// prepend all scripts before the main require js
		$main->parentNode->insertBefore($script, $main);
		
		// remove all modified scripts
		foreach($remove as $script){
			$script->parentNode->removeChild($script); 
		}
		
		$output =  $dom->saveHTML();
		
		// output the final markup - clear whitespace
		return $this->trimWhitespace( $output );
		
	}
	
	function minify( $scripts ){
		
		// sort results
		//ksort_recursive( $minify );
		
		// call google-closure
		foreach( $scripts as $name=>$group ){
			$first = current($group);
			// go to next group if minify flag is not true
			if( !$first["data"]['minify'] ) continue;
			$min = new Minify();
			// get the encoding from the first member of the group
			$encode = $first["data"]["encode"];
			// loop through the group and add the files
			foreach( $group as $script ){
				$file = ( !strpos($script["src"], "http") ) ? $_SERVER['DOCUMENT_ROOT'] . $script["src"] : $script["src"];
				$min->add( $file );
			}
			
			$min		->cacheDir( APP. "public/". $GLOBALS['client']['require']['baseUrl'] )
						->setFile( $name.".min" )
						->quiet()
						->hideDebugInfo();
							
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
				
		}
		
	}
	
	
	function config( $scripts, $dom ){
		
		// first process the require.config.json for cdn libs
		// add a config option for the location of this file? 
		$file = APP. "public/require.config.json";
		if( is_file( $file ) ) $json = file_get_contents( $file );
		if( !empty( $json ) ) $libs = json_decode($json, true);
		
		if( !empty( $libs ) ){ 
			// merge the libs with the client globals
			$GLOBALS['client']['require'] = array_merge($GLOBALS['client']['require'], $libs);
		}
		
		// get the main require js
		$main = $dom->getElementById("require-main");
		// loop through the scripts
		foreach ($scripts as $name=>$group){
			//$first = current($group);
			$attr = $this->groupAttributes($group);
			if( !$attr['data']['require'] ) {
				if( $attr['data']['minify'] ) {
					$file = url($GLOBALS['client']['require']['baseUrl'] . $name .".min.js");
				} else {
					$file = $attr["src"];
				}
				// render a standard script tag
				$script = $dom->createElement('script');
				$script->setAttribute("type", "text/javascript");
				$script->setAttribute("src", $file);
				$script->setAttribute("defer", "defer");
				// prepend all scripts before the main require js
				$main->parentNode->insertBefore($script, $main);

			} else {
				// check the require parameters...
				if( $attr['data']['minify'] ) {
					$name = $name .".min";
				}
				// push the name of the groups as the dependency
				array_push( $GLOBALS['client']['require']['deps'], $name);
				
				// add the shim, if any
				if( !empty($attr['data']['deps']) ) 
					$GLOBALS['client']['require']['shim'][$name] = $attr['data']['deps'];
				
				
			}
			

		}
		
		// return the DOM object
		return $dom;
		
	}
	
	
	
	// Helpers
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
	
	function groupAttributes($group){
		
		$attr = array();
			
		foreach($group as $element){
			$attr = array_merge_recursive($attr, $element);
		}
		
		// marge data values
		foreach($attr['data'] as $key =>$element){
			$attributes = array();
			// don't process items that are already collapsed
			if( !is_array( $element ) ) {
				// explode the string (in case it has comma seperated values)
				if( strpos($element, ",") ) $attr['data'][$key] = explode(",", $element);
				continue;
			}
			foreach($element as $k =>$v){
				$attribute = ( is_array($v) ) ? $v : explode(",", $v) ;
				$attributes = array_merge( $attributes, array_unique($attribute) );
				
			}
			$attr['data'][$key] = array_unique( $attributes );
		}
		
		return $attr;
	}
	
	function trimWhitespace( $string ){
		// replace multiple spaces with one
		return preg_replace( '/\s+/', ' ', $string );
	}
  
}

?>