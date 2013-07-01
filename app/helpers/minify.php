<?php

class Minify extends UglifyJS {

	protected $_content = array();
	protected $_dom;

	function __construct()  {
		// class objects
		$this->cache = new Minify_Cache_File();
	}

	// a version of the "write" function that doesn't output the content
	function create(){

		$cache_file = $this->_getCacheFileName();

		$cache_mtime = ( is_file($cache_file) ) ? @filemtime($cache_file) : false;
		$etag = ( is_file($cache_file) ) ? md5_file($cache_file) : false;

		// flags
		$is_old =  (array_key_exists("HTTP_IF_MODIFIED_SINCE", $_SERVER)) ? @strtotime(@$_SERVER['HTTP_IF_MODIFIED_SINCE']) == $cache_mtime : false;
		$file_diff = (array_key_exists("HTTP_IF_NONE_MATCH", $_SERVER)) ? @trim(@$_SERVER['HTTP_IF_NONE_MATCH']) == $etag : false;
		$needs_compile = $this->_isRecompileNeeded($cache_file);

		if ( $is_old || $file_diff || $needs_compile) {
			$result = $this->_compile();
			file_put_contents($cache_file, $result);
		}

	}

	function js( $dom=false, $file=false ){
		$client = "";
		$group = array();
		$remove = array();
		// make this a config option?
		$baseUrl =  "assets/js/";

		// FIX: create the dir if not available
		//if( !is_dir( APP. "public/". $baseUrl ) ) mkdir(APP. "public/". $baseUrl, 0775, true);
		//if( !is_dir( APP. "public/js/" ) ) mkdir(APP. "public/js/", 0775, true);


		// filter the scripts
		$scripts = $dom->getElementsByTagName('script');

		// check the script attributes
		foreach ($scripts as $script){
			// check out for the supported script attributes
			$data = array();
			$id = $script->getAttribute('id');
			$data['path'] = $script->getAttribute('data-path');
			$data['deps'] = $script->getAttribute('data-deps');
			$data['group'] = $script->getAttribute('data-group');
			$data['order'] = (int) $script->getAttribute('data-order');
			$data['encode'] = $script->getAttribute('data-encode');
			$type = $script->getAttribute('data-type');
			// remove domain name from src (if entered)
			$src = str_replace( array( url(), cdn() ),"/", $script->getAttribute('src') );

			// register types
			$data['minify'] = strpos($type, "minify") > -1 || !empty($data['encode']);
			$data['require'] = strpos($type, "require") > -1 || !empty($data['path']);
			$data['client'] = strpos($type, "client") > -1;

			// leave standard types alone
			if( !$data['minify'] && !$data['require'] && !$data['client']) continue;

			// remove if not the intended container
			if( !$data['group'] || $id != $data['group'] ."-min") $remove[] = $script;

			// if no src add to the config file
			if( empty($src) && ( (!DEBUG && $data['require']) || $data['client']) ) {
				// #94 check dependencies
				if( !empty( $data['deps'] ) ){
					$deps = explode(",", $data['deps']);
					$client .= "require(". json_encode( $deps ) .", function(){ ". $script->textContent ." });";
				} else {
					$client .= $script->textContent;
				}
				// no further processing required
				continue;
			}

			//get the name from the script src
			$name =str_replace( array(WEB_FOLDER.$baseUrl, url(), cdn() ),"", $src);
			// remove the .js extension if not a full path and no alias set (require.js conditions :P)
			if( substr($name, 0,1) !=  "/"  && empty($data['path']) ) $name = substr($name , 0, -3);

			// there is no grouping if there's no minification :P
			if( $data['minify'] && !empty($data['group']) ) {
				$group[$data['group']][] = array( "src" => $src, "data" => $data );
			} else if( $data['minify'] ) {
			//} else if( $data['minify'] && !$data['require'] ) {
				// group all files to be minified in one file (under the template name)
				$group[$file][] = array( "src" => $src, "data" => $data );
			} else {
				$group[$name][] = array( "src" => $src, "data" => $data );
			}


		}


		// process minification
		$group = $this->uglifyJS( $group );
		// process requireJS
		$dom = $this->requireJS( $group, $dom );

		// remove all modified scripts
		foreach($remove as $script){
			$script->parentNode->removeChild($script);
		}

		$GLOBALS['client']["_src"] = $client;


		return $dom;

	}

	function css( $dom=false, $file=false ){
		if(!$dom || !$file) return;

		$el = array();
		$remove = array();
		$http = new Http();
		$http->setMethod('GET');
		// (re)set the source files
		$this->_srcs = array();
		$cache_path = $this->cache->getPath() ."/assets/css/";
		// filter the scripts
		$tags = $dom->getElementsByTagName('link');

		foreach ($tags as $tag){
			$id = $tag->getAttribute('id');
			$rel = $tag->getAttribute('rel');
			$href = $tag->getAttribute('href');
			$type = $tag->getAttribute('data-type');
			$group = $tag->getAttribute('data-group');
			if($rel=="stylesheet" && $type=="minify" && !empty($href) ){
				if( empty($group) ){
					$el[$file][] = $href;
				} else {
					$el[$group][] = $href;
				}
				// remove if not the intended container
				if( !$group || $id != $group ."-min") $remove[] = $tag;
			}
		}

		// process groups seperately
		foreach ($el as $group=>$styles){
			// get the raw css
			$css = "";
			$md5 = "";
			foreach ($styles as $style){
				$result = $http->execute( $style );
				if( $result && !empty($result) ){
					$css .= $result;
				}
			}
			// get the signature
			$md5 .= md5($css);
			// remove comments
			$css = $this->removeCommentsCSS($css);
			// strip whitspace
			$css = $this->trimWhitespace($css);
			$this->_content[$group] = $css;
			$this->_srcs[$group] = $cache_path ."$group.$md5.min.css";
		}

		// remove the 'old' link tags
		foreach ($remove as $tag){
			$tag->parentNode->removeChild($tag);
		}

		// save css in file
		$this->write();
		// update the dom
		$dom = $this->update( $dom );

		return $dom;

	}

	function less( $dom=false, $file=false ){
		// pre-requisites
		if(!$dom || !$file) return false;
		// if in debug no need to change anything
		if( DEBUG ) return $dom;

		$el = array();
		$target = array();
		$less = new lessc;
		$http = new Http();
		$http->setMethod('GET');
		// make this a config option?
		$baseUrl =  "/assets/css/";
		// (re)set the source files
		$this->_srcs = array();
		$cache_path = $this->cache->getPath() . $baseUrl;
		// filter the scripts
		$tags = $dom->getElementsByTagName('link');

		// save the less tags
		foreach ($tags as $tag){
			$rel = $tag->getAttribute('rel');
			if($rel=="stylesheet/less"){
				// save the link for processing
				array_push($target, $tag);
			}
		}

		// process less files
		foreach ($target as $tag){
			// get the raw css
			$css = "";
			$md5 = "";
			$href = $tag->getAttribute('href');
			// check if it's a local url
			$local = (substr($href, 0, 4) !== "http");
			if( $local ) $href = url( $href );
			$result = $http->execute( $href );
			// set the path of the file as the import dir ( code clean up?)
			$importDir = parse_url($href);
			$importDir = substr($importDir['path'], 1, strrpos($importDir['path'], "/") );
			$less->setImportDir( array($importDir) );

			$css = $less->compile( $result );
			// get the signature
			$md5 = md5($css);
			// remove comments
			$css = $this->removeCommentsCSS($css);
			// strip whitspace
			$css = $this->trimWhitespace($css);
			$this->_content[] = $css;
			$filename =  basename($href, ".less").".$md5.min.css";
			$this->_srcs[] = $cache_path . $filename;

			// always leave the less link tags as markup - will be parced by the css()
			// change the attributes to css
			$tag->setAttribute("rel", "stylesheet");
			// change the link to its compiled version
			$tag->setAttribute("href", $baseUrl . $filename);

		}

		// save compiled files
		$this->write();

		// remove any instances of the less lib
		$scripts = $dom->getElementsByTagName('script');
		foreach ($scripts as $script){
			$src = $script->getAttribute('src');
			if (preg_match("/less\.js|less\.min\.js/i", $src)){
				$script->parentNode->removeChild($script);
			}
		}

		return $dom;

	}

	function write() {

		foreach($this->_srcs as $name=>$cache_file){

			$result = $this->_content[$name];

			// No cache directory so just dump the output.
			//if ($this->_cache_dir == "") {
			//	echo $this->_compile();
			//} else {
			// make sure the dir is available
			check_dir( $cache_file, true );
			// check if the existing file is current
			//$cache_file = $this->_getCacheFileName();
			if ($this->_isRecompileNeeded($cache_file)) {
				//$result = $this->_compile();
				file_put_contents($cache_file, $result);
				//echo $result;
			//} else {
				// No recompile needed, but see if we can send a 304 to the browser.
				/*$cache_mtime = filemtime($cache_file);
				$etag = md5_file($cache_file);
				header("Last-Modified: ".gmdate("D, d M Y H:i:s", $cache_mtime)." GMT");
				header("Etag: $etag");
				if (@strtotime(@$_SERVER['HTTP_IF_MODIFIED_SINCE']) == $cache_mtime ||
					@trim(@$_SERVER['HTTP_IF_NONE_MATCH']) == $etag) {
					header("HTTP/1.1 304 Not Modified");
				} else {
					// Read the cache file and send it to the client.
					echo file_get_contents($cache_file);
				}*/
			}
		}
	}

	// update the DOM
	function update( $dom ){

		//main dom containers
		$head = $dom->getElementsByTagName("head")->item(0);
		$require_main = $dom->getElementById("require-main");

		foreach($this->_srcs as $name=>$min_file){

			$file = str_replace( $this->cache->getPath() ."/", "", $min_file);
			// backwards compatibility - remove old path
			$file = str_replace(APP ."public/", "", $file);
			$ext = substr( $file, strrpos($file, ".")+1 );
			// lookup if the container already exists
			$container = $dom->getElementById($name ."-min");

			switch($ext){
				case "css":

					if( is_null($container) ){
						$tag = $dom->createElement('link');
						$tag->setAttribute("type", "text/css");
						$tag->setAttribute("href", cdn($file));
						$tag->setAttribute("rel", "stylesheet");
						$tag->setAttribute("media", "screen");
						// append at the end of the head section
						$head->appendChild($tag);
					} else {
						$container->setAttribute("href", cdn($file));
						// remove data* attributes
						$container->removeAttribute("data-group");
						$container->removeAttribute("data-type");
					}

				break;
				case "js":

					if( is_null($container) ){
						$tag = $dom->createElement('script');
						$tag->setAttribute("type", "text/javascript");
						$tag->setAttribute("src", cdn($file));
						$tag->setAttribute("defer", "defer");
					} else {
						$container->setAttribute("src", cdn($file));
						// remove data* attributes
						$container->removeAttribute("data-group");
						$container->removeAttribute("data-type");
						$container->removeAttribute("data-path");
						$container->removeAttribute("data-deps");
					}
				break;
			}

		}

		return $dom;

	}

	function uglifyJS( $scripts ){
		// make this a config option?
		$baseUrl =  "assets/js/";
		$http = new Http();
		$http->setMethod('GET');
		// sort results
		//ksort_recursive( $minify );
		// record signature
		$md5 = "";
		$cache_path = $this->cache->getPath() ."/$baseUrl";
		// FIX: create the dir if not available
		if( !is_dir( $cache_path ) ) mkdir($cache_path, 0775, true);

		// process each group
		foreach( $scripts as $name=>$group ){
			$first = current($group);
			$result = "";
			// go to next group if minify flag is not true
			if( !$first["data"]['minify'] ) continue;
			$min = new UglifyJS();
			$min->cacheDir( $cache_path );
			$min->compiler( $GLOBALS['config']['compress']['uglify_service'] );
			// get the encoding from the first member of the group
			$encode = $first["data"]["encode"];
			// loop through the group and add the files
			foreach( $group as $script ){
				// the move the domain from the script (if available)
				// check if it's a local url
				$href = $script["src"];
				$local = (substr($href, 0, 4) !== "http" || substr($href, 0, 2) !== "//" );
				if( $local ) $href = url( $href );
				$result .= $http->execute( $href );
				//$src = str_replace( array(url(), cdn() ),"", $script["src"] );
				// remove leading slash
				//$src = ltrim($src,'/');
				//$md5 .= md5_file($file);
			}
			// compress signatures of all files
			$md5 = md5( $result );
			//contents of each group are saved in a tmp file
			$tmp_file = $cache_path . "tmp.$md5.js";
			file_put_contents($tmp_file, $result);
			// add tmp file
			$min->add( $tmp_file );
			$min->setFile( "$name.$md5.min" );
			if( !DEBUG){
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

			$min->write();

			// add the signature in the group
			$scripts[$name][]["data"]["md5"] = $md5;

		}

		return $scripts;

	}

	function requireJS( $scripts, $dom ){

		// loop through the scripts
		foreach ($scripts as $name=>$group){
			//$first = current($group);
			$attr = $this->groupAttributes($group);
			// signature of file/group
			$md5 = ( !empty( $attr['data']['md5'] ) ) ? $attr['data']['md5'] : false;
			// get file of the group
			if( $attr['data']['minify'] ) {
				$file = $GLOBALS['client']['require']['baseUrl'] . $name;
				$file .= ( $md5 ) ? ".". $md5 .".min.js" : ".min.js";
				$file = cdn( $file );
			} else {
				$file = $attr["src"];
			}
			//
			if( !$attr['data']['require'] ) {

				// render a standard script tag
				$script = $dom->createElement('script');
				$script->setAttribute("type", "text/javascript");
				$script->setAttribute("src", $file);
				$script->setAttribute("defer", "defer");
				// add the new script in the dom
				$dom = $this->updateDom($script, $dom);

			} else {
				// check the require parameters...
				if( !empty($attr['data']['path']) ){
					$name = $attr['data']['path'];
				} elseif( $attr['data']['minify'] ) {
					//$name = $name .".min";
					//$name = $name;
				}

				// if there is a signature we'll have to create a new path for the group
				if ( $md5 ){
					$GLOBALS['client']['require']['paths'][$name] =  substr( $file, 0, -3);

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

/*
	function setFile( $name=false ) {
		if($name) $this->_file = $name;
		return $this;
	}

	function _getCacheFileName() {
		return ( empty($this->_file) ) ? $this->_cache_dir . $this->_getHash() . ".js" : $this->_cache_dir . $this->_file. ".js";
	}
*/
	function trimWhitespace( $string, $replace=" " ){
		// replace multiple spaces with one
		return preg_replace( '/\s+/', $replace, $string );
	}

	function removeCommentsCSS( $string ){
		$regex = array(
			"!/\*.*?\*/!s"=>'',
			"/\n\s*\n/"=>"\n"
			);
		$string = preg_replace(array_keys($regex),$regex,$string);

		return $string;
	}
	// deprecate...
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

	function getCache($path ){
		$cache = new Minify_Cache_File();
		// check if the file is less than an hour old
		//return ( $cache->isValid($path, time("now")-3600) ) ? $cache->fetch($path) : false;
		return $cache->fetch($path);
	}
	function setCache($path, $content){
		$cache = new Minify_Cache_File();
		$cache->store($path, $content);
	}

}

?>