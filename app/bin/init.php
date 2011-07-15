<?php

require( getPath('bin/config.php') );
require( getPath('bin/mvc.php') );

	
// Includes
require( getPath('models/Page.php') );

//===============================================
// Includes
//===============================================
require( getPath('helpers/common.php') );
require( getPath('helpers/language.php') );
require( getPath('helpers/main_menu.php') );



//===============================================
// Session
//===============================================
session_start();

//===============================================
// Uncaught Exception Handling
//===============================================s
set_exception_handler('uncaught_exception_handler');

function uncaught_exception_handler($e) {
  ob_end_clean(); //dump out remaining buffered text
  $vars['message']=$e;
  die(View::do_fetch( getPath('views/errors/exception_uncaught.php'),$vars));
}

function custom_error($msg='') {
  $vars['msg']=$msg;
  die(View::do_fetch( getPath('views/errors/custom_error.php'),$vars));
}

//===============================================
// Database
//===============================================
function getdbh( $db=null ) {
  // generate the name prefix
  $db_name = "db_" . substr( $db, 0, stripos($db, ".") );
  if (!isset($GLOBALS[ $db_name ]))
    try {
      $GLOBALS[ $db_name ] = new PDO('sqlite:'. DATA . $db);
      //$GLOBALS['dbh'] = new PDO('mysql:host=localhost;dbname=dbname', 'username', 'password');
    } catch (PDOException $e) {
      die('Connection failed: '.$e->getMessage());
    }
  return $GLOBALS[ $db_name ];
}

//===============================================
// Autoloading for Business Classes
//===============================================
// Assumes Model Classes start with capital letters and Helpers start with lower case letters
function __autoload($classname) {
  $a=$classname[0];
  if ($a >= 'A' && $a <='Z')
    require_once( getPath('models/'.$classname.'.php') );
  else
    require_once( getPath('helpers/'.$classname.'.php') );  
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

function getDomain(){
	$domain = 'http://'.$_SERVER['SERVER_NAME'];
	if( $_SERVER['SERVER_PORT'] != 80 ){ 
		// add server port to the domain
		$domain .= ":".$_SERVER['SERVER_PORT'];
	}
	return $domain;
}


?>