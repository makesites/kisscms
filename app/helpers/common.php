<?php

//===============================================
// Helper Functions
//===============================================


// Custom parser for KISSCMS
// checking for a controller that matches the first path - the "main" controller is used as a fallback
function findController($url) {
	// first remove the website path from the URL
	$requri=preg_replace('#^'.addslashes(WEB_FOLDER).'#', '', $url);
	// now split the path to two parts - the first is the controller, the second it's parameters
	preg_match('#^([^/]+)/?(.*)$#', $requri, $matches);
	// fix - remove last match if empty
	if(isset($matches[count($matches)-1]) && $matches[count($matches)-1]==''){ array_pop( $matches ); }
	//
	// first match is always the contoller - add it if it exists, is made out of alphanumeric chars and is not empty...
	$controller = (isset($matches[1]) && preg_match('#^[A-Za-z0-9_\-\.]+$#', $matches[1]) && !empty($matches[1])) ? $matches[1]: false;
	// check if the controller exists
	$controllerfile = getPath('controllers/'.$controller.'.php');
	// check if what we found is sane
	if (!$controller || !file_exists($controllerfile)){
		// find the default controller
		if( defined("DEFAULT_ROUTE") ){
			$controller = DEFAULT_ROUTE;
			$controllerfile = getPath('controllers/'.$controller.'.php');
		}
	}
	if( !empty( $controllerfile) ) {
		// set the controller file as a constant for later use (only do it the first time...)
		if( !defined("CONTROLLER") ) define("CONTROLLER", $controller);
		// include the controller file
		require( $controllerfile );
		// return the controller name with the first letter uppercase
		return ucfirst( $controller );
	}
}

// Get the output from the file in the public folders
function isStatic( $file ) {
	// FIX: Bail out if this is the root
	if( $file == WEB_FOLDER) return false;
	// FIX: clean webfolder from path before comparing
	$file = preg_replace('#^'.addslashes(WEB_FOLDER).'#', '', $file);
	$root = rtrim($_SERVER['DOCUMENT_ROOT'],"/")."/";

	// check in the document root
	if ( file_exists( $root.$file ) ) {
		$target = $root.$file;
		return $target;
	}
	// check in the app public folders
	if( defined("APP") ){
		if ( file_exists( APP."public/".$file ) ) {
			$target = APP."public/".$file;
			return $target;
		}
		if (is_dir(APP."plugins/") && $handle = opendir(APP."plugins/")) {
			while (false !== ($plugin = readdir($handle))) {
				if ($plugin == '.' || $plugin == '..') {
				  continue;
				}
				if ( is_dir(APP."plugins/".$plugin) && file_exists( APP."plugins/".$plugin."/public/".$file ) ) {
					$target = APP."plugins/".$plugin."/public/".$file;
					return $target;
				}
			}
		}
	}
	// check in the base public folders
	if( defined("BASE") ){
		if ( file_exists( BASE."public/".$file ) ) {
			$target = BASE."public/".$file;
			return $target;
		}
		if (is_dir(BASE."plugins/") && $handle = opendir(BASE."plugins/")) {
			while (false !== ($plugin = readdir($handle))) {
				if ($plugin == '.' || $plugin == '..') {
				  continue;
				}
				if ( is_dir(BASE."plugins/".$plugin) && file_exists( BASE."plugins/".$plugin."/public/".$file ) ) {
					$target = BASE."plugins/".$plugin."/public/".$file;
					return $target;
				}
			}
		}
	}
	// check in the plugins directory
	if( defined("PLUGINS")){
		$files = glob(PLUGINS."*/public/$file");
		if( $files && count($files) > 0 ) {
			// arbitrary pick the first file - should have a comparison mechanism in place
			$target = $files[0];
			return $target;
		}
	}
	# 110 looking into web root for plugins
	if( is_dir( SITE_ROOT . "/plugins" ) ){
		$files = glob(SITE_ROOT . "/plugins/*/public/$file");
		if( $files && count($files) > 0 ) {
			// arbitrary pick the first file - should have a comparison mechanism in place
			$target = $files[0];
			return $target;
		}
	}
	//lastly check the cache (less than an hour old)
	$cache = new Minify_Cache_File();
	//if( $cache->isValid($file, time("now")-3600) ) return $cache->tmp() ."/". $file;
	if( $cache->isValid($file, 0) ) return $cache->tmp() ."/". $file;

	// return false if there are no results
	return false;
}

function getFile($filename) {
	$exts = explode(".", strtolower($filename) ) ;
	$ext = array_pop( $exts );
	switch ($ext) {
		case "txt": $ctype="text/plain"; break;
		case "css": $ctype="text/css"; break;
		case "js": $ctype="application/javascript"; break;
		case "pdf": $ctype="application/pdf"; break;
		case "exe": $ctype="application/octet-stream"; break;
		case "zip": $ctype="application/zip"; break;
		case "doc": $ctype="application/msword"; break;
		case "rtf": $ctype="application/rtf"; break;
		case "xls": $ctype="application/vnd.ms-excel"; break;
		case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
		case "gif": $ctype="image/gif"; break;
		case "png": $ctype="image/png"; break;
		case "jpeg":
		case "jpg": $ctype="image/jpg"; break;
		default: $ctype="text/html";
	}

	// FIX: exit if this is a directory
	if( is_dir($filename) ) return false;

	$output = file_get_contents( $filename );
	header("Content-Type: $ctype");
	$mtime = filemtime($filename);
	$etag = md5_file($filename);
	header("Last-Modified: ".gmdate("D, d M Y H:i:s", $mtime)." GMT");
	header("Etag: $etag");
	return $output;
}

function getPath( $file ) {
	if (defined("APP")){
		// find the clone file first
		if (file_exists(APP.$file)) return APP.$file;
		if (file_exists(APP."plugins/$file")) return APP."plugins/$file";
		// check the plugins folder - return the first match
		$search = glob(APP."plugins/*/$file", GLOB_BRACE);
		if($search) return array_pop($search);
	}
	// try the base folder if we didn't find anything
	if( defined("BASE") ) {
		// find the core file second
		if (file_exists(BASE.$file)) return BASE.$file;
		if (file_exists(BASE."plugins/$file")) return BASE."plugins/$file";
		// check the plugins folder
		$search = glob(BASE."plugins/*/$file", GLOB_BRACE);
		if($search) return array_pop($search);
	}
	// check the plugins folder if we still haven't found anything
	if( defined("PLUGINS") ){
		// find the plugins file
		if (file_exists(PLUGINS.$file)) return PLUGINS.$file;
		// check the plugins folder
		$search = glob(PLUGINS."*/$file", GLOB_BRACE);
		if($search) return array_pop($search);
	}
	# 110 looking into web root for plugins
	if( is_dir( SITE_ROOT . "/plugins" ) ){
		// find the plugins file
		if (file_exists(SITE_ROOT ."/plugins/". $file)) return SITE_ROOT ."/plugins/". $file;
		// check the plugins folder
		$search = glob(SITE_ROOT ."/plugins/*/$file", GLOB_BRACE);
		if($search) return array_pop($search);
	}
	// nothing checks out...
	return false;
}

function url($file='', $cdn=false){
	// get the full uri for the file
	$uri = uri($file);
	// check if it is a static
	if( $cdn && defined("CDN") && isStatic( $uri )){
		// load the cdn address instead
		// remove trailing slash, if any
		$domain = ( substr(CDN, -1) == "/" ) ? substr(CDN, 0, -1) : CDN;

	} else {
		// check if this is a secure connection
		$domain = ( $_SERVER['SERVER_PORT'] == "443" || (defined('SSL') && SSL) ) ? 'https://' : 'http://';
		// load the regular server address
		$domain .= ( substr($_SERVER['SERVER_NAME'], -1) == "/" ) ? substr($_SERVER['SERVER_NAME'], 0, -1) : $_SERVER['SERVER_NAME'];
		// add server port to the domain if not the default one
		/*if( $_SERVER['SERVER_PORT'] != "80" && $_SERVER['SERVER_PORT'] != "443" ){
			$domain .= ":".$_SERVER['SERVER_PORT'];
		}*/
	}

	// add the uri
	$url = $domain . $uri;

	return $url;
}

function uri($file=''){
	// remove leading slash, if any
	$file = ( substr($file, 0, 1) == "/" ) ? substr($file, 1) : $file;
	// add the web folder
	$uri = WEB_FOLDER;
	// add file if available
	if( $file != '' ){
		$uri .= $file;
	}
	return $uri;
}

function cdn($file=''){
	// get the full uri for the file
	$uri = uri($file);
	// first check if we have already defined a CDN
	if (defined("CDN")){
		// remove trailing slash, if any
		$url = ( substr(CDN, -1) == "/" ) ? substr(CDN, 0, -1) : CDN;
		// #108 remove www from cdn address (if set by SERVER_NAME)
		$url = str_replace("www.",'',$url);
		return $url . $uri;
	} else {
		// fallback to the domain name
		return url($file);
	}
}

// return an html attribute for a value
function attr($name=false, $value=false){
	if(!$name || !$value || empty($value)) return;
	return $name .'="'. $value .'"';
}

// find all files with a certain name
function findFiles($filename) {
	$return = array();
	// first find the files in the app directory
	if (defined("APP")){
		$files = glob(APP."{views/*/$filename,plugins/*/views/$filename}",GLOB_BRACE);
		if( is_array( $files) ){
			$return = array_merge($return, $files);
		}
	}
	// then find the files in the base directory, if available
	if (defined("BASE")){
		$files = glob(BASE."{views/*/$filename,plugins/*/views/$filename}",GLOB_BRACE);
		foreach( $files as $file ){
			$app_file = str_replace(BASE, APP, $file);
			if( !in_array($app_file, $return) ){
				$return[] = $file;
			}
		}
	}
	if (defined("PLUGINS")){
		$files = glob(PLUGINS."*/views/$filename");
		if( is_array( $files) ){
			$return = array_merge($return, $files);
		}
	}
	# 110 looking into web root for plugins
	if( is_dir( SITE_ROOT . "/plugins" ) ){
		$files = glob(SITE_ROOT . "/plugins/*/views/$filename");
		if( is_array( $files) ){
			$return = array_merge($return, $files);
		}

	}
	return $return;
}


function writeFile($file = false, $output=false, $method='w'){

	if ($file){
		// try to find the directory, create it if not avaiable
		$dir = dirname($file);
		if( is_dir( $dir ) ){

		} else {
			mkdir($dir, 0777);
		}
		// switch between methods to write the file
		switch( $method ) {
			case 'w':
				$f = fopen($file,"w");
				fwrite($f,$output);
				fclose($f);
			break;

			case 'w9':
				$gz_output = gzcompress($output, 9);
				$gz_file = gzopen($file, "w9");
				gzwrite($gz_file, $gz_output);
				gzclose($gz_file);
			break;

		}
	}
}



// clean submitted input
function clean( $data ){
	if( is_array($data) ){
		foreach($data as $k=>$v){
			$data[$k] = trim(filter_var($v, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES));
		}
	} else {
		$data = trim(filter_var($data, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES));
	}
	return $data;
}


// Original PHP code by Chirp Internet: www.chirp.com.au
// Please acknowledge use of this code by including this header.

function truncate($string, $limit, $break=".", $pad="...")
{
  // return with no change if string is shorter than $limit
  if(strlen($string) <= $limit) return $string;

  // is $break present between $limit and the end of the string?
  if(false !== ($breakpoint = strpos($string, $break, $limit))) {
	if($breakpoint < strlen($string) - 1) {
	  $string = substr($string, 0, $breakpoint) . $pad;
	}
  }

  return $string;
}


function beautify($string, $block='.', $ucwords=true)
{

  // stop in the occurance of the designated character
  if( $block ){
	$string = substr( $string, 0 , strpos($string, $block) );
  }
  // replace all underscores with spaces
  $string = str_replace( "_", " ", $string );

  if($ucwords){
	  $string = ucwords ( $string );
  }
  return $string;
}

// get the "pure" request URI (compared to the web folder)
function request_uri(){
 return preg_replace('#^'.addslashes(WEB_FOLDER).'#', '', $_SERVER['REQUEST_URI']);
}

// collapse the elements of an array to the elements of it's children
function array_collapse( $params ){
	$collapsed = array();
	foreach( $params as $key => $val ){
		if( is_array($val) ){
			$collapsed = array_merge($collapsed, $val);
		} else {
			$collapsed[$key] = $val;
		}
	}
	return $collapsed;
}

// remove a set of elements from a multi-dimentional array
function array_remove( $array, $values ){
	foreach($array as $k=>$v){
		if( is_array($v) ) {
			$array[$k] = array_remove( $v, $values );
		} else if( $v && in_array( $v, $values) ){
			unset($array[$k]);
		}
	}
	return $array;
}

// convert the keys to elements of the array
function array_flatten( $array ){
	$result = array();

	foreach( $array as $k=>$v ){
		$result[] = $k;
		$result[] = $v;
	}

	return $result;
}

// checks if a given directory exists and optionally creates it
function check_dir( $file=false, $create=false, $chmod=0755 ){
	// prerequisites
	if(!$file) return;
	// break the file
	$info = pathinfo($file);
	$exists = is_dir($info['dirname']);
	// return now if we don't have to create the dir
	if(!$create || $exists) return $exists;

	if( !$exists ) {
		$dirs = explode("/", $info['dirname']);
		$path = "/";
		foreach( $dirs as $folder){
			//$path .= array_shift($dirs) ."/";
			if(empty($folder)) continue;
			$path .= $folder ."/";
			// create each dir (if not available)
			if( !is_dir( $path ) ) @mkdir($path, $chmod, true);
		}

	}
	// assuming that all the folders missing are created...
	return true;
}

// Get a normalized numeric epoch timestamp in microseconds
function timestamp(){
	$timestamp = (string) $_SERVER['REQUEST_TIME'];
	// aws:#5 include microseconds when calculating REQUEST_TIME in PHP < 5.4
	if( strlen($timestamp) == 10 ) $timestamp .= "000";
	return $timestamp;
}

/**
 * Function to calculate date or time difference.
 *
 * Function to calculate date or time difference. Returns an array or
 * false on error.
 *
 * @author       J de Silva                             <giddomains@gmail.com>
 * @copyright    Copyright &copy; 2005, J de Silva
 * @link         http://www.gidnetwork.com/b-16.html    Get the date / time difference with PHP
 * @param        string                                 $start
 * @param        string                                 $end
 * @return       array
 */
function get_time_difference( $start, $end )
{
	$uts['start']      =    strtotime( $start );
	$uts['end']        =    strtotime( $end );
	if( $uts['start']!==-1 && $uts['end']!==-1 )
	{
		if( $uts['end'] >= $uts['start'] )
		{
			$diff    =    $uts['end'] - $uts['start'];
			if( $days=intval((floor($diff/86400))) )
				$diff = $diff % 86400;
			if( $hours=intval((floor($diff/3600))) )
				$diff = $diff % 3600;
			if( $minutes=intval((floor($diff/60))) )
				$diff = $diff % 60;
			$diff    =    intval( $diff );
			return( array('days'=>$days, 'hours'=>$hours, 'minutes'=>$minutes, 'seconds'=>$diff) );
		}
		else
		{
			trigger_error( "Ending date/time is earlier than the start date/time", E_USER_WARNING );
		}
	}
	else
	{
		trigger_error( "Invalid date/time data detected", E_USER_WARNING );
	}
	return( false );
}

/********************************
 * Retro-support of get_called_class()
 * Tested and works in PHP 5.2.4
 * http://www.sol1.com.au/
 ********************************/
if(!function_exists('get_called_class')) {
function get_called_class($bt = false,$l = 1) {
	if (!$bt) $bt = debug_backtrace();
	if (!isset($bt[$l])) throw new Exception("Cannot find called class -> stack level too deep.");
	if (!isset($bt[$l]['type'])) {
		throw new Exception ('type not set');
	}
	else switch ($bt[$l]['type']) {
		case '::':
			$lines = file($bt[$l]['file']);
			$i = 0;
			$callerLine = '';
			do {
				$i++;
				$callerLine = $lines[$bt[$l]['line']-$i] . $callerLine;
			} while (stripos($callerLine,$bt[$l]['function']) === false);
			preg_match('/([a-zA-Z0-9\_]+)::'.$bt[$l]['function'].'/',
						$callerLine,
						$matches);
			if (!isset($matches[1])) {
				// must be an edge case.
				throw new Exception ("Could not find caller class: originating method call is obscured.");
			}
			switch ($matches[1]) {
				case 'self':
				case 'parent':
					return get_called_class($bt,$l+1);
				default:
					return $matches[1];
			}
			// won't get here.
		case '->': switch ($bt[$l]['function']) {
				case '__get':
					// edge case -> get class of calling object
					if (!is_object($bt[$l]['object'])) throw new Exception ("Edge case fail. __get called on non object.");
					return get_class($bt[$l]['object']);
				default: return $bt[$l]['class'];
			}

		default: throw new Exception ("Unknown backtrace method type");
	}
}
}


// Backwards compatibility

// In PHP 5.4, you can use JSON_UNESCAPED_SLASHES:
//echo json_encode($this->stream, JSON_UNESCAPED_SLASHES);
// Otherwise, you have to do some trivial post-processing
function json_encode_escaped($string){
	$find = array('\\/', "\n");
	$use = array('/', "");
	return str_replace($find, $use, json_encode($string));
}





// DEPRECATED

function myUrl($path='',$fullurl=true){
	$url = '';
	// first check if we want the full url
	if( $fullurl ){
		$url = 'http://'.$_SERVER['SERVER_NAME'];
		// add server port to the domain if not the default one
		if( $_SERVER['SERVER_PORT'] != "80" ){
			$url .= ":".$_SERVER['SERVER_PORT'];
		}
		//if( $_SERVER['REMOTE_PORT'] != 80 ){
		//	$url .= ":".$_SERVER['REMOTE_PORT'];
		//}
	}
	// add the web folder
	$url .= WEB_FOLDER;
	// add path if available
	if( $path != '' ){
		$url .= $path;
	}

	// FIX: Remove the ending slash
	$url = ( substr($url, -1) == "/" ) ? substr($url, 0, -1) : $url;

	return $url;
}


function myCDN(){
	// first check if we have already defined a CDN
	if (defined("CDN")){
		// remove trailing slash, if any
		$url = ( substr(CDN, -1) == "/" ) ? substr(CDN, 0, -1) : CDN;
		return $url;
	} else {
		// fallback to the domain name
		return myUrl();
	}
}

// converts a query string to an associative array
function query_to_array( $string, $flat=false ){
	$queries = array();
	$pairs = explode("&", $string);
	foreach( $pairs as $pair){
		$kv = explode("=", $pair);
		// if flat flag set then create a one-dimensional array
		if( $flat ) {
			$queries = array_merge( $queries, $kv );
		} else {
			$queries[ $kv[0] ] = $kv[1];
		}
	}
	return $queries;
}

function ksort_recursive(&$array, $sort_flags = SORT_REGULAR) {
	if (!is_array($array)) return false;
	ksort($array, $sort_flags);
	foreach ($array as &$arr) {
		ksort_recursive($arr, $sort_flags);
	}
	return true;
}

function encode($string="",$base=36,$key="KISSCMS") {
	// variables
	$j=0;
	$hash="";
	$key = sha1($key);
	$strLen = strlen($string);
	$keyLen = strlen($key);
	for ($i = 0; $i < $strLen; $i++) {
		$ordStr = ord(substr($string,$i,1));
		if ($j == $keyLen) { $j = 0; }
		$ordKey = ord(substr($key,$j,1));
		$j++;
		$hash .= strrev(base_convert(dechex($ordStr + $ordKey),16,$base));
	}
	return $hash;
}

function decode($string="",$base=36,$key="KISSCMS") {
	// variables
	$j=0;
	$hash="";
	$key = sha1($key);
	$strLen = strlen($string);
	$keyLen = strlen($key);
	for ($i = 0; $i < $strLen; $i+=2) {
		$ordStr = hexdec(base_convert(strrev(substr($string,$i,2)),$base,16));
		if ($j == $keyLen) { $j = 0; }
		$ordKey = ord(substr($key,$j,1));
		$j++;
		$hash .= chr($ordStr - $ordKey);
	}
	return $hash;
}

/*
function redirect($url,$alertmsg='') {
  if ($alertmsg)
	addjAlert($alertmsg,$url);
  header('Location: '.myUrl($url));
  exit;
}
*/
//session must have started
//$uri indicates which uri will activate the alert (substring check)

/*
function addjAlert($msg,$uri='') {
  if ($msg) {
	$s="alert(\"$msg\");";
	$_SESSION['jAlert'][]=array($uri,$s);
  }
}

function getjAlert() {
  if (!isset($_SESSION['jAlert']) || !$_SESSION['jAlert'])
	return '';
  $pageuri=$_SERVER['REQUEST_URI'];
  $current=null;
  $remainder=null;
  foreach ($_SESSION['jAlert'] as $x) {
	$uri=$x[0];
	if (!$uri || strpos($pageuri,$uri)!==false)
	  $current[]=$x[1];
	else
	  $remainder[]=$x;
  }
  if ($current) {
	if ($remainder)
	  $_SESSION['jAlert']=$remainder;
	else
	  unset($_SESSION['jAlert']);
	return '<script type="text/javascript">'."\n".implode("\n",$current)."\n</script>\n";
  }
  return '';
}
*/

?>
