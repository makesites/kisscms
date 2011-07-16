<?php


//===============================================
// Global Variables
//===============================================


//===============================================
// Functions
//===============================================


// Custom parser for KISSCMS 
// the "main" controller is used for all URLs except the once's that match existing controllers
function requestParserCustom(&$controller,&$action,&$params) {
  // firsth remove the website path from the URL
  $requri=preg_replace('#^'.addslashes(WEB_FOLDER).'#', '', $_SERVER['REQUEST_URI']);
  // now split the path to two parts - the first is the controller, the second it's parameters
  preg_match('#^([^/]+)/?(.*)$#', $requri, $matches);
  // fix - remove last match if empty
  if(isset($matches[count($matches)-1]) && $matches[count($matches)-1]==''){ array_pop( $matches ); }
  // first match is always the contoller
  $controller = (isset($matches[1])) ? $matches[1]: null;
  // second match is action or with params
  $params_pos = (isset($matches[2])) ? strpos($matches[2], "/"): false;
  if($params_pos !== false){ 
	$matches[3] = substr($matches[2], $params_pos+1);
	$matches[2] = substr($matches[2], 0, $params_pos);
  }
  // check if the controller exists
  $controllerfile= getPath('controllers/'.$controller.'.php');
  if (preg_match('#^[A-Za-z0-9_-]+$#',$controller) && file_exists($controllerfile)){
	// we split the path to seperate parameters
	$action= (isset($matches[2])) ? $matches[2] : DEFAULT_ACTION;
	$params= (isset($matches[3])) ? explode('/',$matches[3]) : null;
  } else {
    // pass all other requests to the "main" controller
	$controller= DEFAULT_ROUTE;
	$action= DEFAULT_ACTION;
	if( $controller=="main" ){
		// remove the main controller from the path
		array_shift( $matches );
	}
	$params= implode("/", $matches);
	//print_r( $matches );
	//exit;
  }
}

// Get the output from the file in the public folders
function isStatic( $file ) {
	// check in the base public folders
	if( defined("BASE") ){
		if ( file_exists( BASE."public".$_SERVER['REQUEST_URI'] ) ) {
			$target = BASE."public/".$_SERVER['REQUEST_URI'];
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
	// check in the app public folders
	if( defined("APP") ){
		if ( file_exists( APP."public".$_SERVER['REQUEST_URI'] ) ) {
			$target = APP."public/".$_SERVER['REQUEST_URI'];
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
	// check in the document root
	if ( file_exists( $_SERVER['DOCUMENT_ROOT'].$file ) ) {
		$target = $_SERVER['DOCUMENT_ROOT'].$file;
	}
	
	// output the results
	if( isset($target) ){
		return file_get_contents( $target );
	}else {
		return false;
	}
}


function getPath( $file ) {
	if (defined("APP") && file_exists(APP.$file)){ 
		// find the clone file first
		return APP.$file;
	} elseif (defined("BASE") && file_exists(BASE.$file)) {
		// find the core file second
		return BASE.$file;
	} else {
	   // nothing checks out - output the same...
	   return $file;
	}
}

function getURL($path='',$fullurl=false){
	$url = '';
	// first check if we want the full url
	if( $fullurl ){ 
		$url = 'http://'.$_SERVER['SERVER_NAME'];
		// add server port to the domain if not the default one
		if( $_SERVER['SERVER_PORT'] != 80 ){ 
			$url .= ":".$_SERVER['SERVER_PORT'];
		}
	}
	// add trailing slash
	$url .= '/';
	// add path if available
	if( $path != '' ){ 
		$url .= WEB_FOLDER.$path;
	}
  	return $url;
}

function redirect($url,$alertmsg='') {
  if ($alertmsg)
    addjAlert($alertmsg,$url);
  header('Location: '.myUrl($url));
  exit;
}

function require_login() {
  if (!isset($_SESSION['admin']))
    redirect('admin/login');
}

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