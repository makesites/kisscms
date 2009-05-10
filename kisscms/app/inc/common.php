<?php

//===============================================
// Global Variables
//===============================================

// establish the container for all our page content
$data = array();

//===============================================
// Functions
//===============================================

function myUrl($url='',$fullurl=false) {
  $s=$fullurl ? WEB_DOMAIN : '';
  $s.=WEB_FOLDER.$url;
  return $s;
}

// Custom parser for KISSCMS
function requestParserCustom(&$controller,&$action,&$params) {
  $requri=preg_replace('#^'.addslashes(WEB_FOLDER).'#', '', $_SERVER['REQUEST_URI']);
  preg_match('#^([^/]+)\/{0,1}$#', $requri, $matches);
  if (count($matches)==2) {
    $controller=$matches[1];
  } else {
    preg_match('#^([^/]+)/([^/]+)/?(.*)$#', $requri, $matches);
    if (isset($matches[1]))
      $controller=$matches[1];
    if (isset($matches[2])){
	  // the "main" controller will always uses the index() function and passes the 2nd part as a parameter
	  if( $controller== "main" ){
		$action="index";
		$params= explode('/',$matches[2]);
	  } else {
		$action=$matches[2];
	  }
	}
    if (isset($matches[3]) && $matches[3]){
	  // the "main" controller will combine the 2nd & 3rd pars and pass them as the path of our page
	  if( $controller== "main" ){
		$params= $matches[2] . '/' . $matches[3];
	  } else {
	    $params=explode('/',$matches[3]);
	  }
	}
  }
  if (!preg_match('#^[A-Za-z0-9_-]+$#',$action) || function_exists($action))
    die(viewFetch('404.php'));
}

function require_login() {
  if (!isset($_SESSION['kisscms_admin'])) {
    header('Location: '.myUrl('cms/login'));
    exit;
  }
}

function getGlobals() {
    $dbh = getdbh();
    $sql = 'SELECT * FROM "config"';
    $results = $dbh->query($sql);
	while ($variable = $results->fetch(PDO::FETCH_ASSOC)) {
		$GLOBALS['config'][$variable['name']]=$variable['value'];
	};  
}

//===============================================
// Database
//===============================================
function getdbh() {

  if (!isset($GLOBALS['dbh']))
    try {
      $GLOBALS['dbh'] = new PDO('sqlite:'.DATABASE_FILE);
    } catch (PDOException $e) {
      die('Connection failed: '.$e->getMessage());
    }
  return $GLOBALS['dbh'];
}

?>