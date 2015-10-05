<?php
/**
 * PHP class for JavaScript minification  using UglifyJS as a service.
 * https://github.com/makesites/uglifyjs-php
 *
 * Created by Makis Tracend (@tracend)
 * Distributed through [Makesites.org](http://makesites.org/)
 * Released under the [Apache License v2.0](http://makesites.org/licenses/APACHE-2.0)
 */
class UglifyJS {

	var $_srcs = array();
	var $_mode = "WHITESPACE_ONLY";
	var $_warning_level = "DEFAULT";
	var $_pretty_print = false;
	var $_debug = true;
	var $_cache_dir = "";
	var $_code_url_prefix = "";
	var $_timestamp = 0;
	var $_compiler = array(
		"host" => "marijnhaverbeke.nl",
		"port" => "80",
		"path" => "/uglifyjs"
	);

	function UglifyJS() { }

	/**
	 * Adds a source file to the list of files to compile. Files will be
	 * concatenated in the order they are added.
	 */
	function add($file) {
		$this->_srcs[] = $file;
		return $this;
	}

	/**
	 * Sets the directory where the compilation results should be cached
	 */
	function cacheDir($dir) {
		$this->_cache_dir = $dir;
		return $this;
	}

	function compiler( $string ) {
		// get the previous compiler
		$compiler = $this->_compiler;
		$url = parse_url( $string );
		// gather vars
		if( array_key_exists("host", $url) ) $compiler['host'] = $url['host'];
		if( array_key_exists("port", $url) ) $compiler['port'] = $url['port'];
		if( array_key_exists("path", $url) ) $compiler['path'] = $url['path'];
		// save back the compiler
		$this->_compiler = $compiler;
		return $compiler;
	}

	function setFile( $name=false ) {
		if($name) $this->_file = $name;
		return $this;
	}
	/**
	 * Sets the URL prefix to use with the UglifyJS service's code_url
	 * parameter.
	 *
	 * Using code_url tells the compiler service the URLs of the scripts to
	 * fetch. The file paths added in add() must therefore be relative to this
	 * URL.
	 *
	 * Example usage:
	 *
	 * $c->add("js/my-app.js")
	 *   ->add("js/popup.js")
	 *   ->useCodeUrl('http://www.example.com/app/')
	 *   ->cacheDir("/tmp/js-cache/")
	 *   ->write();
	 *
	 * This assumes your PHP script is in a directory /app/ and that the JS is in
	 * /app/js/ and accessible via HTTP.
	 */
	function useCodeUrl($code_url_prefix) {
		$this->_code_url_prefix = $code_url_prefix;
		return $this;
	}

	/**
	 * Tells the compiler to pretty print the output.
	 */
	function prettyPrint() {
		$this->_pretty_print = true;
		return $this;
	}

	/**
	 * Turns of the debug info.
	 * By default statistics, errors and warnings are logged to the console.
	 */
	function hideDebugInfo() {
		$this->_debug = false;
		return $this;
	}

	/**
	 * Sets the compilation mode to optimize whitespace only.
	 */
	function whitespaceOnly() {
		$this->_mode = "WHITESPACE_ONLY";
		return $this;
	}

	/**
	 * Sets the compilation mode to simple optimizations.
	 */
	function simpleMode() {
		$this->_mode = "SIMPLE_OPTIMIZATIONS";
		return $this;
	}

	/**
	 * Sets the compilation mode to advanced optimizations (recommended).
	 */
	function advancedMode() {
		$this->_mode = "ADVANCED_OPTIMIZATIONS";
		return $this;
	}

	/**
	 * Gets the compilation mode from the URL, set the mode param to
	 * 'w', 's' or 'a'.
	 */
	function getModeFromUrl() {
		if ($_GET['mode'] == 's') $this->simpleMode();
		else if ($_GET['mode'] == 'a') $this->advancedMode();
		else $this->whitespaceOnly();
		return $this;
	}

	/**
	 * Sets the warning level to QUIET.
	 */
	function quiet() {
		$this->_warning_level = "QUIET";
		return $this;
	}

	/**
	 * Sets the default warning level.
	 */
	function defaultWarnings() {
		$this->_warning_level = "DEFAULT";
		return $this;
	}

	/**
	 * Sets the warning level to VERBOSE.
	 */
	function verbose() {
		$this->_warning_level = "VERBOSE";
		return $this;
	}

	/**
	 * Writes the compiled response.  Reading from either the cache, or
	 * invoking a recompile, if necessary.
	 */
	function write( $output=false ) {

		// No cache directory so just dump the output.
		if ($this->_cache_dir == "") {
			echo $this->_compile();

		} else {
			$cache_file = $this->_getCacheFileName();
			if ($this->_isRecompileNeeded($cache_file)) {
				$result = $this->_compile();
				file_put_contents($cache_file, $result);
				if( $output ){
					echo $result;
				}
			} else {
				// No recompile needed, but see if we need to output the cached file.
				if( $output ){
					// Read the cache file and send it to the client.
					echo file_get_contents($cache_file);
				}
			}
		}
	}

	// removes source files (usually after compilation)
	function clear(){
		foreach ($this->_srcs as $i => $src) {
			unlink($src);
			unset($this->_srcs[$i]);
		}
	}

	// set a timestamp to compare the compiling against
	function timestamp( $time = null ){
		// prerequisite
		if( !is_int ( $time ) ) return;
		$this->_timestamp =  $time;
	}

	// ----- Privates -----

	function _isRecompileNeeded($cache_file) {
		// If there is no cache file, we obviously need to recompile.
		if (!file_exists($cache_file)) return true;

		$cache_mtime = filemtime($cache_file);

		// #1 If a specific time is set, use that as a reference
		if ( !empty( $this->_timestamp ) ) return ( $this->_timestamp > $cache_mtime );

		// #2 If the source files are newer than the cache file, recompile.
		foreach ($this->_srcs as $src) {
			if (filemtime($src) > $cache_mtime) return true;
		}

		// #3 If this script calling the compiler is newer than the cache file,
		// recompile.  Note, this might not be accurate if the file doing the
		// compilation is loaded via an include().
		if (filemtime($_SERVER["SCRIPT_FILENAME"]) > $cache_mtime) return true;

		// Cache is up to date.
		return false;
	}

	function _compile() {
		// No debug info?
		$result = $this->_makeRequest();
		return $result;
	}

	function _getCacheFileName() {
		return ( empty($this->_file) ) ? $this->_cache_dir . $this->_getHash() . ".js" : $this->_cache_dir . $this->_file. ".js";
	}

	function _getHash() {
		return md5(implode(",", $this->_srcs) . "-" .
				$this->_mode . "-" .
				$this->_warning_level . "-" .
				$this->_pretty_print . "-" .
				$this->_debug);
	}

	function _getParams() {
		$params = array();
		foreach ($this->_getParamList() as $key => $value) {
			$params[] = preg_replace("/_[0-9]$/", "", $key) . "=" . urlencode($value);
		}
		return implode("&", $params);
	}

	function _getParamList() {
		$params = array();
		if ($this->_code_url_prefix) {
			// Send the URL to each source file instead of the raw source.
			$i = 0;
			foreach($this->_srcs as $file){
				$params["code_url_$i"] = $this->_code_url_prefix . $file;
				$i++;
			}
		} else {
			$params["js_code"] = $this->_readSources();
		}
		$params["compilation_level"] = $this->_mode;
		$params["output_format"] = "xml";
		$params["warning_level"] = $this->_warning_level;
		if ($this->_pretty_print) $params["formatting"] = "pretty_print";
		$params["output_info_1"] = "compiled_code";
		$params["output_info_2"] = "statistics";
		$params["output_info_3"] = "warnings";
		$params["output_info_4"] = "errors";
		return $params;
	}

	function _readSources() {
		$code = "";
		foreach ($this->_srcs as $src) {
			$code .= file_get_contents($src) . "\n\n";
		}
		return $code;
	}

	function _makeRequest() {
		$data = $this->_getParams();
		//$referer = @$_SERVER["HTTP_REFERER"] or "";
		$referer = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
		// variables
		extract($this->_compiler);

		$fp = fsockopen($host, $port);
		if (!$fp) {
			throw new Exception("Unable to open socket");
		}

		if ($fp) {
			fputs($fp, "POST $path HTTP/1.1\r\n");
			fputs($fp, "Host: $host\r\n");
			fputs($fp, "Referer: $referer\r\n");
			fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
			fputs($fp, "Content-length: ". strlen($data) ."\r\n");
			fputs($fp, "Connection: close\r\n\r\n");
			fputs($fp, $data);

			$result = "";
			while (!feof($fp)) {
				$result .= fgets($fp, 128);
			}

			fclose($fp);
		}

		$data = substr($result, (strpos($result, "\r\n\r\n")+4));
		if (strpos(strtolower($result), "transfer-encoding: chunked") !== FALSE) {
			$data = $this->_unchunk($data);
		}

		return $data;
	}

	function _unchunk($data) {
		$fp = 0;
		$outData = "";
		while ($fp < strlen($data)) {
			$rawnum = substr($data, $fp, strpos(substr($data, $fp), "\r\n") + 2);
			$num = hexdec(trim($rawnum));
			$fp += strlen($rawnum);
			$chunk = substr($data, $fp, $num);
			$outData .= $chunk;
			$fp += strlen($chunk);
		}
		return $outData;
	}

}
