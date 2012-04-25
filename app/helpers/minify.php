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
	
	function setFile( $name=false ) {
		if($name) $this->_file = $name;
    	return $this;
	}
	
	function _getCacheFileName() {
		return ( empty($this->_file) ) ? $this->_cache_dir . $this->_getHash() . ".js" : $this->_cache_dir . $this->_file. ".js";
	}
  
}

?>