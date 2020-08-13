<?php

//===============================================================
// Template
//===============================================================
class Template extends KISS_View {
	public $hash;
	private $template;
	private $client;

	//Example of overriding a constructor/method, add some code then pass control back to parent
	function __construct( $vars=array() ) {
		// defaults
		$this->vars = array(
			"body" => "",
			"_page" => array(
				"view" => ""
			)
		);
		$this->client = array();
		$this->vars = array_merge($this->vars, $vars);

		$this->hash = $this->getHash("", $vars);
		$file = $this->getTemplate();

		parent::__construct($file, $this->vars);
	}

	public static function output($vars=''){
		// variables
		$template = new Template($vars);
		$cache = null;
		$id = "{$_SERVER['HTTP_HOST']}/html/". $template->hash;
		// get available cache (if applicable)
		if( $template->canCache() )
			// first thing, check if there's a cached version of the template
			$cache = self::getCache( $id );
		//$cache = false;
		if( !empty($cache) && !DEBUG) { echo $cache; return; }
		// continue processing
		$template->setupClient();
		//
		$GLOBALS['body'] = $template->vars["body"];
		$GLOBALS['head'] = $template->get("head");
		$GLOBALS['foot'] = $template->get("foot");

		// compile the page with the existing data
		$output = $template->do_fetch($template->file, $template->vars);

		// post-process (in debug with limited features)
		$output = $template->process($output);
		// output the final markup - clear whitespace (if not in debug mode)
		echo $output;
		// set the cache for later use
		self::setCache( $id, $output);
	}

	public static function head( $vars=false ){

		$data = $GLOBALS['head'];

		foreach($data as $name=>$html){
			echo "$html\n";
		}
	}

	public static function body($view=false){
		$data = $GLOBALS['body'];
		if( empty($data) ) return;
		foreach($data as $part){
			if( $view && !isset($part['status']) ){
				View::do_dump( getPath('views/main/body-'. $view .'.php'), $part);
			} elseif( array_key_exists('view', $part) ){
				View::do_dump( $part['view'], $part);
			} else {
				View::do_dump( getPath('views/main/body.php'), $part);
			}
		}
	}

	public static function foot($vars=false){
		$views = $GLOBALS['foot'];
		// FIX: render main foot last
		if( array_key_exists('main', $views) ){
			$main = $views['main'];
			unset($views['main']);
		}
		foreach($views as $name=>$html){
			echo "$html\n";
		}
		if( isset($main) ){
			echo $main ."\n";
		}
	}

	static function do_fetch($file='',$vars='') {
		$view = parent::do_fetch($file,$vars);
		// post-event
		Event::trigger('template:parse', $view, $vars );
		return $view;
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
		$min = new Minify();

		// map the dom
		$dom = new DOMDocument;
		$dom->preserveWhiteSpace = false;
		@$dom->loadHTML($html);

		// minification
		if( !DEBUG ) {
			$dom = $min->less($dom, $this->template);
			$dom = $min->css($dom, $this->template);
			$dom = $min->js($dom, $this->template);
		} else if( $this->useRequire() ) {
			// require is "on" in debug mode
			$dom = $min->requireDebug($dom, $this->template);
		}
		// process require configuration
		$this->createClient($dom);

		$output = $dom->saveHTML();

		// output the final markup - minify if not in debug
		return  ( DEBUG ) ? $output : $min->html( $output );

	}


	// Helpers
	function getHash( $prefix="", $vars=array() ){
		// OLD
		// the hash is an expression of the variables compiled to render the template
		// note that constantly updated values (like timestamps) should be avoided to allow the hash to be reproduced...
		//$string = serialize( $vars );

		// NEW method
		// the hash is a combination of :
		// - the protocol
		// - the request url
		// - the request parameters
		// - the session id
		//
		$protocol = ( isSSL() ) ? "secure": "public";
		// FIX: reject cache for changed user state
		$user = ( array_key_exists('user', $_SESSION ) && isset($_SESSION['user']['id']) ) ? $_SESSION['user']['id'] : 0;
		// use serialize( $_REQUEST ) instead?
		$key = session_id() . $user. json_encode($_REQUEST) . $protocol . $_SERVER['REQUEST_URI'];
		// generate a hash form the string
		return $prefix . hash("md5", $key);
	}

	static function getCache($file ){
		$cache = new Minify_Cache_File();
		$dir = dirname( $file );
		$cache_path = $cache->getPath() ."/$dir";

		// FIX: create the dir if not available
		if( !is_dir( $cache_path ) ) mkdir($cache_path, 0775, true);

		// check if the file is less than an hour old
		return ( $cache->isValid($file, time("now")-3600) ) ? $cache->fetch($file) : false;
	}
	static function setCache($file, $data){
		$cache = new Minify_Cache_File();
		$cache->store($file, $data);
	}
	function canCache(){
		// currently only admin urls are excluded, in the future this may be extensible
		$valid = ( strpos($_SERVER['REQUEST_URI'], "/admin/") !== 0 );
		return $valid;
	}

	function getTemplate(){
		// support for mobile template
		if(array_key_exists('IS_MOBILE', $GLOBALS) && $GLOBALS['IS_MOBILE'] == true && is_file(TEMPLATES."mobile.php") ){
			$this->template = "mobile";
		} else {
			$template = (array_key_exists('template', $this->vars) && is_file(TEMPLATES.$this->vars['template'])) ? $this->vars['template'] : DEFAULT_TEMPLATE;
			// strip out the php extension (if supplied)
			$this->template = str_replace(".php", "", $template);
		}
		// #124 - fallback to the default template of the base folder
		$default = realpath(BASE. "../public/templates/default.php");

		return ( is_file(TEMPLATES.$this->template.".php") ) ? TEMPLATES.$this->template.".php" : $default;
	}

	// find the section a view file belongs to
	function getSection( $file ){
		// first check if it is in a plugins folder
		if( preg_match('/[a-z0-9_.\/\\\]plugins[a-z0-9_.\/\\\]*$/i', $file, $match) ) {
			// $match =  /plugins/{section}/views/file.php
			$path = explode("/", $match[0]);
			// the location of the section is rather hardcoded here but there must be a better way...
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

		// first, process the require.config.json for cdn libs
		$file = isStatic( "require.config.json" );
		if( is_file( $file ) ) $json = file_get_contents( $file );
		$libs = ( !empty( $json ) ) ? json_decode($json, true) : array();

		if( $this->useRequire() ){
			// merge the libs with the client globals
			$GLOBALS['client']['require'] = array_merge($GLOBALS['client']['require'], $libs);
		} else {
			$this->client['require'] = $libs;
		}
	}

	function createClient( $dom ){
		$client = "";
		// see if there is any "loose" source in the client
		if( !empty($GLOBALS['client']["_src"]) ) {
			$client = $GLOBALS['client']["_src"];
			unset($GLOBALS['client']["_src"]);
		}
		// if in debug, remove any scripts in the require.js paths
		$scripts = ( !empty($this->client['require']['paths']) );
		if( (DEBUG || !$this->useRequire() ) && $scripts ){
			// add the scripts in the require list as script tags
			$head = $dom->getElementsByTagName("head")->item(0);

			foreach( $this->client['require']['paths'] as $name => $script){
				$src = ( is_array( $script ) ) ? array_shift($script) : $script;
				// check if there's a js extension
				if( substr($src, -3) != ".js") $src .= ".js";

				// add straight in the head section
				$script = $dom->createElement('script');
				$script->setAttribute("type", "text/javascript");
				$script->setAttribute("src", $src);
				$head->appendChild($script);
				unset($this->client['require']['paths'][$name]);
			}

		}
		// render the global client vars
		$client .= 'Object.extend(KISSCMS, '. json_encode_escaped( $GLOBALS['client'] ) .');';
		if( $this->useRequire() ){
			$client .= 'if(typeof require != "undefined") require.config( KISSCMS["require"] );';
		}
		$client = $this->trimWhitespace($client);
		// #87 not caching client vars as a file
		/*
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
		*/
		/*
		$client_file = "client";
		$client_src= WEB_FOLDER. $client_file;

		// Always render the client.js
		// render a standard script tag
		$script = $dom->createElement('script');
		$script->setAttribute("type", "text/javascript");
		$script->setAttribute("src", $client_src);
		//$script->setAttribute("defer", "defer");
		// include the script
		$dom = $this->updateDom($script, $dom);
		*/
		// set the client as a session var
		if( !array_key_exists("_client", $_SESSION) ) $_SESSION["_client"] = array();
		$_SESSION["_client"][$_SERVER["REQUEST_URI"]] = $client;
	}

	public static function doList( $selected=null){

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
		// replace multiple spaces with one (except textarea)
		return preg_replace( '/(?:\s+(?![^<]*<\/(textarea|pre)>))/', ' ', $string );
	}

	function useRequire(){
		return defined("REQUIRE") || !DEBUG;
	}

}

?>
