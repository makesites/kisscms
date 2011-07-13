<?php

//===============================================
// KISSCMS Settings (please configure)
//===============================================

define('WEB_DOMAIN','http://'.$_SERVER['SERVER_NAME']); //with http:// and NO trailing slash pls

define('DEFAULT_ROUTE','page');
define('DEFAULT_ACTION','index');


//===============================================
// Website Info
//===============================================
$GLOBALS['config']['sitename']="KISSCMS";
$GLOBALS['config']['username']="admin";
$GLOBALS['config']['password']="admin";


//===============================================
// Debug
//===============================================
ini_set('display_errors','On');
error_reporting(E_ALL);


//===============================================
// Includes
//===============================================
require( getPath('inc/mvc.php') );
require( getPath('inc/common.php') );
require( getPath('inc/language.php') );
require( getPath('inc/modules.php') );
require( getPath('inc/functions.php') );
require( getPath('models/Page.php') );

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
function getdbh( $db ) {
  if (!isset($GLOBALS['dbh']))
    try {
      $GLOBALS['dbh'] = new PDO('sqlite:'. DATA . $db);
      //$GLOBALS['dbh'] = new PDO('mysql:host=localhost;dbname=dbname', 'username', 'password');
    } catch (PDOException $e) {
      die('Connection failed: '.$e->getMessage());
    }
  return $GLOBALS['dbh'];
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

?>