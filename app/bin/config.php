<?php

//===============================================
// KISSCMS Settings (please configure)
//===============================================

// Definitions
define('WEB_DOMAIN', getDomain() ); //with http:// and NO trailing slash pls

define('DEFAULT_ROUTE','page');
define('DEFAULT_ACTION','index');

define('DB_PAGES', "pages.sqlite"); 


//===============================================
// Website Info
//===============================================
$GLOBALS['admin']['username']="admin";
$GLOBALS['admin']['password']="admin";


//===============================================
// Debug
//===============================================
ini_set('display_errors','On');
error_reporting(E_ALL);


?>