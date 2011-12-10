<?php

//===============================================
// Helper Functions
//===============================================


// Custom parser for KISSCMS 
// the "main" controller is used for all URLs except the once's that match existing controllers
function findController($url) {
	// first remove the website path from the URL
	$requri=preg_replace('#^'.addslashes(WEB_FOLDER).'#', '', $url);
	// now split the path to two parts - the first is the controller, the second it's parameters
	preg_match('#^([^/]+)/?(.*)$#', $requri, $matches);
	// fix - remove last match if empty
	if(isset($matches[count($matches)-1]) && $matches[count($matches)-1]==''){ array_pop( $matches ); }
	// first match is always the contoller - add it if it exists, is made out of alphanumeric chars and is not empty...
	$controller = (isset($matches[1]) && preg_match('#^[A-Za-z0-9_-]+$#', $matches[1]) && !empty($matches[1])) ? $matches[1]: false;
	// check if the controller exists
	$controllerfile = getPath('controllers/'.$controller.'.php');
	// check if what we found is sane
	if (!$controller || !file_exists($controllerfile)){
		// find the default controller 
		$controller = DEFAULT_ROUTE;
		$controllerfile = getPath('controllers/'.$controller.'.php');
	}
	var_dump($GLOBALS['config']);
	// include the controller file 
	require( $controllerfile );
	// return the controller name with the first letter uppercase
	return ucfirst( $controller );
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
				if ( is_dir($plugin) && file_exists( APP."plugins/".$plugin."/public/".file ) ) {
					$target = APP."plugins/".$plugin."/public/".file;
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
				if ( is_dir($plugin) && file_exists( BASE."plugins/".$plugin."/public/".file ) ) {
					$target = BASE."plugins/".$plugin."/public/".file;
					return $target;	
				}
			}
		}
	}
	// check in the plugins directory
	if( defined("PLUGINS")){
		$files = glob(PLUGINS."*/public/$file");
		if( count($files) > 0 ) {
			// arbitrary pick the first file - should have a comparison mechanism in place
			$target = $files[0];
			return $target;
		}
	}
	
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
    return $output;
} 
 
function getPath( $file ) {
	if (defined("APP")){ 
		// find the clone file first
		if (file_exists(APP.$file)){ 
			return APP.$file;
		// check the plugins folder
		} elseif ( is_dir(APP."plugins/") && $handle = opendir(APP."plugins/")) {
			// check if this is a plugin path
			if (file_exists(APP."plugins/".$file))
				return APP."plugins/".$file;
			// check inside the plugins
			while (false !== ($plugin = readdir($handle))) {
				if ($plugin == '.' || $plugin == '..') { 
				  continue; 
				} 
				if ( is_dir($plugin) && file_exists( APP."plugins/".$plugin."/".$file ) ) {
					return APP."plugins/".$plugin."/".$file;
				}
			}
			
		}
	}
	// try the base folder if we didn't find anything
	if( defined("BASE") ) {
		// find the core file second
		if (file_exists(BASE.$file)){ 
			return BASE.$file;
		// check the plugins folder
		} elseif ( is_dir(BASE."plugins/") && $handle = opendir(BASE."plugins/")) {
			// check if this is a plugin path
			if (file_exists(BASE."plugins/".$file)) 
				return BASE."plugins/".$file;
			// check inside the plugins
			while (false !== ($plugin = readdir($handle))) {
				if ($plugin == '.' || $plugin == '..') { 
				  continue; 
				} 
				if ( is_dir($plugin) && file_exists( BASE."plugins/".$plugin."/".$file ) ) {
					return BASE."plugins/".$plugin."/".$file;
				}
			}
			
		}
	} 
	// check the plugins folder if we still haven't found anything
	if( defined("PLUGINS") ){
		// find the core file second
		if (file_exists(PLUGINS.$file)){ 
			return PLUGINS.$file;
		// check the plugins folder
		} elseif ($handle = opendir(PLUGINS)) {
			while (false !== ($plugin = readdir($handle))) {
				if ($plugin == '.' || $plugin == '..') { 
				  continue; 
				} 
				if ( is_dir($plugin) && file_exists( PLUGINS.$plugin."/".$file ) ) {
					return PLUGINS.$plugin."/".$file;
				}
			}
		}
	} 
	
	// nothing checks out...
	return false;
}


function myUrl($path='',$fullurl=true){
	$url = '';
	// first check if we want the full url
	if( $fullurl ){ 
		$url = 'http://'.$_SERVER['SERVER_NAME'];
		// add server port to the domain if not the default one
		if( $_SERVER['SERVER_PORT'] != 80 ){ 
			$url .= ":".$_SERVER['SERVER_PORT'];
		}
	}
	// add the web folder
	$url .= WEB_FOLDER;
	// add path if available
	if( $path != '' ){ 
		$url .= $path;
	}
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