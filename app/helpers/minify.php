<?php

class Minify extends PhpClosure {
	
	protected $_content = array();
	protected $_dom;
	
	// a version of the "write" function that doesn't output the content
	function create(){
		
		$cache_file = $this->_getCacheFileName();
		
		$cache_mtime = ( is_file($cache_file) ) ? filemtime($cache_file) : false;
		$etag = ( is_file($cache_file) ) ? md5_file($cache_file) : false;
		
		// flags
		$is_old = @strtotime(@$_SERVER['HTTP_IF_MODIFIED_SINCE']) == $cache_mtime;
		$file_diff = @trim(@$_SERVER['HTTP_IF_NONE_MATCH']) == $etag;
		$needs_compile = $this->_isRecompileNeeded($cache_file);
		
		if ( $is_old || $file_diff || $needs_compile) { 
			$result = $this->_compile();
			file_put_contents($cache_file, $result);
		}
		
	}
	
	function css( $dom=false, $file=false ){
		if(!$dom || !$file) return;
		
		$el = array();
		$remove = array();
		$http = new Http();
		$http->setMethod('GET');
		// (re)set the source files
		$this->_srcs = array();
		
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
					$el[][] = $href;
				} else {
					$el[$group][] = $href;
				}
				// remove if not the intended container
				if( $id != $group ."-min") $remove[] = $tag;
			}
		}
		
		// process groups seperately 
		foreach ($el as $group=>$styles){
			// get the raw css
			$css = "";
			foreach ($styles as $style){
				$result = $http->execute( $style );
				if( $result && !empty($result) ){
					$css .= $result;
				}
			}
			// remove comments
			$css = $this->removeCommentsCSS($css);
			// strip whitspace
			$css = $this->trimWhitespace($css);
			$this->_content[$group] = $css;
			$this->_srcs[$group] = APP ."public/assets/css/". $group .".min.css";
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
			
			$file = str_replace(APP ."public/", "", $min_file);
			$ext = substr( $file, strrpos($file, ".")+1 );
			// lookup if the container already exists
			$container = $dom->getElementById($name ."-min");
					
			switch($ext){
				case "css":
					
					if( is_null($container) ){ 
						$tag = $dom->createElement('link');
						$tag->setAttribute("type", "text/css");
						$tag->setAttribute("href", url($file));
						$tag->setAttribute("rel", "stylesheet");
						$tag->setAttribute("media", "screen");
						// append at the end of the head section
						$head->appendChild($tag);
					} else {
						$container->setAttribute("href", url($file));
						// remove data* attributes
						$container->removeAttribute("data-group");
						$container->removeAttribute("data-type");
					}
					
				break;
				case "js":
					
					if( is_null($container) ){ 
						$tag = $dom->createElement('script');
						$tag->setAttribute("type", "text/javascript");
						$tag->setAttribute("src", url($file));
						$tag->setAttribute("defer", "defer");
					} else {
						$container->setAttribute("src", url($file));
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
	
	// Helpers
	function setFile( $name=false ) {
		if($name) $this->_file = $name;
    	return $this;
	}
	
	function _getCacheFileName() {
		return ( empty($this->_file) ) ? $this->_cache_dir . $this->_getHash() . ".js" : $this->_cache_dir . $this->_file. ".js";
	}
  
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
}

?>