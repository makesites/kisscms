<?php
/*****************************************************************
KISSCMS -  http://kisscms.com
Copyright 2011 - Makis Tracend (makis@makesit.es)

Based on the KISSMVC -  http://kissmvc.com
Copyright 2009 -Eric Koh (erickoh75@gmail.com)
*****************************************************************/

//===============================================
// Config
//===============================================
require('../app/inc/config.php');

//===============================================
// Includes
//===============================================
require(APP_PATH.'inc/mvc.php');
require(APP_PATH.'inc/common.php');
require(APP_PATH.'inc/language.php');
require(APP_PATH.'inc/modules.php');
require_once(APP_PATH.'models/Page.php');
require('../app/inc/functions.php');

//===============================================
// Session
//===============================================
session_start();

//===============================================
// Globals
//===============================================
$GLOBALS['sitename']='KISSCMS - Lightweight CMS plugged on the KISSMVC Framework';

//===============================================
// Uncaught Exception Handling
//===============================================s
set_exception_handler('uncaught_exception_handler');

function uncaught_exception_handler($e) {
  ob_end_clean(); //dump out remaining buffered text
  $vars['message']=$e;
  die(View::do_fetch(APP_PATH.'errors/exception_uncaught.php',$vars));
}

function custom_error($msg='') {
  $vars['msg']=$msg;
  die(View::do_fetch(APP_PATH.'errors/custom_error.php',$vars));
}

//===============================================
// Database
//===============================================
function getdbh() {
  if (!isset($GLOBALS['dbh']))
    try {
      $GLOBALS['dbh'] = new PDO('sqlite:'.DB_PATH);
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
    require_once(APP_PATH.'models/'.$classname.'.php');
  else
    require_once(APP_PATH.'helpers/'.$classname.'.php');  
}

//===============================================
// Start the controller
//===============================================
$controller = new Controller(APP_PATH.'controllers/',WEB_FOLDER,'main','index');

?>