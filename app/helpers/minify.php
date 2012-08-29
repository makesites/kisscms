<?php

class Minify extends PhpClosure {
	
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
		
		$styles = array();
		$remove = array();
		$css = "";
		$http = new Http();
		$http->setMethod('GET');
		// (re)set the source files
		$this->_srcs = array();
		
		// filter the scripts
		$tags = $dom->getElementsByTagName('link');
 		
		foreach ($tags as $tag){
			$rel = $tag->getAttribute('rel');
			$href = $tag->getAttribute('href');
			$type = $tag->getAttribute('data-type');
			if($rel=="stylesheet" && $type=="minify" && !empty($href) ){ 
				$styles[] = $href;
				$remove[] = $tag;
				
			}
		}
		
		// get the raw css
		foreach ($styles as $style){
			$result = $http->execute( $style );
			if( $result && !empty($result) ){
				$css .= $result;
			}
		}
		
		// remove the 'old' link tags
		foreach ($remove as $tag){
			$tag->parentNode->removeChild($tag); 
		}
		
		$this->_srcs[] = APP. "public/". $file;
		
		// remove comments
		$css = $this->removeCommentsCSS($css);
		// strip whitspace
		$css = $this->trimWhitespace($css);
		// save css in file
		$this->write( $css );
		
		return $dom;
		
	}
	
	function write( $result="" ) {
		
		foreach($this->_srcs as $cache_file){
			
			//$this->setHeader($cache_file);
			
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
			} else {
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