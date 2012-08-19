<?php

//===============================================================
// Template
//===============================================================
class Template extends KISS_View {
	public $hash;
	
	//Example of overriding a constructor/method, add some code then pass control back to parent
	function __construct($vars='') {
		$this->vars = $vars;
		$this->hash = $this->getHash("", $vars);
		$file = $this->getTemplate();
		parent::__construct($file,$vars);
	}

	function output($vars=''){
		$template = new Template($vars);
		// first thing, check if there's a cached version of the template
		$id = "template_". $template->hash;
		$cache = self::getCache( $id );
		if($cache && !DEBUG) { echo $cache; return; }
		// continue processing
		$template->setupClient();
		$GLOBALS['body'] = $template->vars["body"];
		$GLOBALS['head'] = $template->get("head");
		$GLOBALS['foot'] = $template->get("foot");
		// compile the page with the existing data
		$output = parent::do_fetch($template->file, $template->vars);
		// post-process (in debug with limited features)
		$output = $template->process($output);
		// output the final markup - clear whitespace (if not in debug mode)
		echo $output;
		// set the cache for later use
		self::setCache( $id, $output);
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
		$url = url();
		$group = array();
		$remove = array();
		// make this a config option?
		$baseUrl =  "assets/js/";
		// FIX: create the dir if not available
		if( !is_dir( APP. "public/". $baseUrl ) ) mkdir(APP. "public/". $baseUrl, 0775, true);
		if( !is_dir( APP. "public/js/" ) ) mkdir(APP. "public/js/", 0775, true);
		
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
			$type = $script->getAttribute('data-type');
			// remove domain name from src (if entered)
			$src = str_replace( $url,"/", $script->getAttribute('src') );
			
			// register types
			$data['minify'] = strpos($type, "google-closure") > -1 || !empty($data['encode']);
			$data['require'] = strpos($type, "require") > -1 || !empty($data['path']);
			
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
			$name = substr( str_replace( array(WEB_FOLDER.$baseUrl, url(), cdn() ),"", $src), 0, -3);
			
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
		
		// render the global client vars
		$client .= 'Object.extend(KISSCMS, '. json_encode_escaped( $GLOBALS['client'] ) .');';
		
		$client = $this->trimWhitespace($client);
		$client_file = "client_". $this->hash .".js";
		$cache = $this->getCache( $client_file );
		
		// write config file
		$client_sign = md5($client);
		$cache_sign = ($cache) ? md5($cache) : NULL;
		
		// the client file should not be cached by the cdn
		$client_src= WEB_FOLDER. $client_file;
		
		// check md5 signature
		if($client_sign == $cache_sign){ 
			// do nothing
		} else {
			
			// set the cache for later use
			self::setCache( $client_file , $client);
		}
		// render a standard script tag
		$script = $dom->createElement('script');
		$script->setAttribute("type", "text/javascript");
		$script->setAttribute("src", $client_src);
		$script->setAttribute("defer", "defer");
		// include the script 
		$dom = $this->updateDom($script, $dom);
		
		// remove all modified scripts
		foreach($remove as $script){
			$script->parentNode->removeChild($script); 
		}
		
		$output =  $dom->saveHTML();
		
		// output the final markup - clear whitespace
		return ( DEBUG ) ? $html : $this->trimWhitespace( $output );
		
	}
	
	function minify( $scripts ){
		// make this a config option?
		$baseUrl =  "assets/js/";
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
				// the move the domain from the script (if available)
				$src = str_replace( array(url(), cdn() ),"", $script["src"] );
				$file = $_SERVER['DOCUMENT_ROOT'] . WEB_FOLDER . $src;
				$min->add( $file );
			}
			
			$min		->cacheDir( APP. "public/". $baseUrl )
						->setFile( $name.".min" );
			if( !DEBUG){
			$min		->quiet()
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
				
		}
		
	}
	
	
	function config( $scripts, $dom ){
		
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
				// add the new script in the dom
				$dom = $this->updateDom($script, $main);

			} else {
				// check the require parameters...
				if( !empty($attr['data']['path']) ){
					$name = $attr['data']['path'];
				} elseif( $attr['data']['minify'] ) {
					$name = $name .".min";
				}
				
				// push the name of the groups as the dependency
				array_push( $GLOBALS['client']['require']['deps'], $name);
				
				if( !empty($attr['data']['path']) ) 
						$GLOBALS['client']['require']['paths'][$attr['data']['path']] =  substr( $attr['src'], 0, -3);
					
				// add the shim, if any
				if( !empty($attr['data']['deps']) )
					$GLOBALS['client']['require']['shim'][$name] = (is_array($attr['data']['deps'])) ? $attr['data']['deps'] : array($attr['data']['deps']);
				
			}
			

		}
		
		// return the DOM object
		return $dom;
		
	}
	
	// Helpers
	function getHash( $prefix="", $vars=array() ){
		// the hash is an expression of the variables compiled to render the template
		// note that constantly updated values (like timestamps) should be avoided to allow the hash to be reproduced...
		$string = serialize( $vars );
		// ALTERNATE method
		// the hash is a combination of :
		// - the request url 
		// - the request parameters
		// - the session id
		// - the user id (if available)
		//$string = $_SERVER['REQUEST_URI'];
		//$string .= serialize( $_REQUEST );
		//$string .= session_id();
		//if( isset($_SESSION['user']['id']) ) $string .= $_SESSION['user']['id'];
		// generate a hash form the string
		return $prefix . hash("md5", $string);
	}
	function getCache($id ){
		$cache = new Minify_Cache_File();
		// check if the file is less than an hour old 
		return ( $cache->isValid($id, time("now")-3600) ) ? $cache->fetch($id) : false;
	}
	function setCache($id, $data){
		$cache = new Minify_Cache_File();
		$cache->store($id, $data);
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
	
	// this method compiles vars that need to be available on the client
	function setupClient(){
		
		// make this a config option?
		$baseUrl =  "assets/js/";
		// precaution(s) in case this is the first time we are accessing the client globals (not needed?)
		if( !isset($GLOBALS['client']) ) $GLOBALS['client'] = array();
		if( !isset($GLOBALS['client']['require']) ) $GLOBALS['client']['require'] = array();
		// default require strucure
		$GLOBALS['client']['require'] = array(
			"baseUrl" => WEB_FOLDER . $baseUrl, 
			"paths" => array(),
			"shim" => array(),
			"deps" => array()
		);
		
		// currently there is no support for the require config during DEBUG
		if( !DEBUG ){ 
			// first process the require.config.json for cdn libs
			// add a config option for the location of this file? 
			$file = APP. "public/require.config.json";
			if( is_file( $file ) ) $json = file_get_contents( $file );
			if( !empty( $json ) ) $libs = json_decode($json, true);
			
			if( !empty( $libs ) ){ 
				// merge the libs with the client globals
				$GLOBALS['client']['require'] = array_merge($GLOBALS['client']['require'], $libs);
			}
		}
	}
	
	function doList( $selected=null){
		
		$data['template']['selected'] = $selected;
		
		if ($handle = opendir(TEMPLATES)) {
			while (false !== ($template = readdir($handle))) {
				#52: Skip files that start with a dot
				if ( substr($template,0,1) == '.' ) { 
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
				// fix nested empty arrays manually
				if( implode($attributes) == "") $attributes = array();
			}
			// pickup only the unique values (and reset the keys)
			$attr['data'][$key] = array_values( array_unique( $attributes ) );
		}
		
		return $attr;
	}
	
	function updateDom($tag, $dom){ 
		// switch based on the type of tag (script,link)
		// if link....
		// else
		// get the main require js
		$main = $dom->getElementById("require-main");
		$body = $dom->getElementsByTagName("body")->item(0);
		
		// prepend all scripts before the main require js
		( empty($main) ) 
					? $body->appendChild($tag)
					: $main->parentNode->insertBefore($tag, $main);
					
		
		return $dom;
	}
	
	function trimWhitespace( $string ){
		// replace multiple spaces with one
		return preg_replace( '/\s+/', ' ', $string );
	}
  
}

?>