<?php


//===============================================
// Global Variables
//===============================================


//===============================================
// Functions
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
	// first match is always the contoller
	$controller = (isset($matches[1])) ? $matches[1]: null;
	// check if the controller exists
	$controllerfile= getPath('controllers/'.$controller.'.php');
	if (preg_match('#^[A-Za-z0-9_-]+$#',$controller) && file_exists($controllerfile)){
		// do nothing
	} else {
		// pass all other requests to the "main" controller
		$controller= DEFAULT_ROUTE;
		$controllerfile= getPath('controllers/'. DEFAULT_ROUTE .'.php');;
	}
	
	// ultimately include the controller file 
	require( $controllerfile );
	// return the controller name with the first letter uppercase
	return ucfirst( $controller );
}

// Get the output from the file in the public folders
function isStatic( $file ) {
	// FIX: Bail out if this is the root
	if( $file == WEB_FOLDER) return false;
	
	// check in the document root
	if ( file_exists( $_SERVER['DOCUMENT_ROOT'].$file ) ) {
		$target = $_SERVER['DOCUMENT_ROOT'].$file;
	// check in the app public folders
	} elseif( defined("APP") ){
		if ( file_exists( APP."public".$_SERVER['REQUEST_URI'] ) ) {
			$target = APP."public".$_SERVER['REQUEST_URI'];
		}
		if ($handle = opendir(APP."plugins/")) {
			while (false !== ($plugin = readdir($handle))) {
				if ($plugin == '.' || $plugin == '..') { 
				  continue; 
				} 
				if ( is_dir($plugin) && file_exists( APP."plugins/".$plugin."/public".file ) ) {
					$target = APP."plugins/".$plugin."/public".file;
				}
			}
		}
	}
	// check in the base public folders
	elseif( defined("BASE") ){
		if ( file_exists( BASE."public".$_SERVER['REQUEST_URI'] ) ) {
			$target = BASE."public".$_SERVER['REQUEST_URI'];
		}
		if ($handle = opendir(BASE."plugins/")) {
			while (false !== ($plugin = readdir($handle))) {
				if ($plugin == '.' || $plugin == '..') { 
				  continue; 
				} 
				if ( is_dir($plugin) && file_exists( BASE."plugins/".$plugin."/public".file ) ) {
					$target = BASE."plugins/".$plugin."/public".file;
				}
			}
		}
	}
	
	// output the results
	if( isset($target) ){
		return $target;
	}else {
		return false;
	}
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

	$output = file_get_contents( $filename );
	header("Content-Type: $ctype"); 
    return $output;
} 
 
function getPath( $file ) {
	if (defined("APP") && file_exists(APP.$file)){ 
		// find the clone file first
		if (file_exists(APP.$file)){ 
			return APP.$file;
		// check the plugins folder
		} else if ($handle = opendir(APP."plugins/")) {
			while (false !== ($plugin = readdir($handle))) {
				if ($plugin == '.' || $plugin == '..') { 
				  continue; 
				} 
				if ( is_dir($plugin) && file_exists( APP."plugins/".$plugin."/".$file ) ) {
					return APP."plugins/".$plugin."/".$file;
				}
			}
			
		}
	} elseif (defined("BASE") && file_exists(BASE.$file)) {
		// find the core file second
		if (file_exists(BASE.$file)){ 
			return BASE.$file;
		// check the plugins folder
		} else if ($handle = opendir(BASE."plugins/")) {
			while (false !== ($plugin = readdir($handle))) {
				if ($plugin == '.' || $plugin == '..') { 
				  continue; 
				} 
				if ( is_dir($plugin) && file_exists( BASE."plugins/".$plugin."/".$file ) ) {
					return BASE."plugins/".$plugin."/".$file;
				}
			}
			
		}
	} else {
	   // nothing checks out - output the same...
	   return $file;
	}
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
	// add path if available
	if( $path != '' ){ 
		$url .= WEB_FOLDER.$path;
	}
  	return $url;
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

?>