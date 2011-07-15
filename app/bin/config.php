<?php

//===============================================
// KISSCMS Settings (please configure)
//===============================================

define('WEB_DOMAIN','http://'.$_SERVER['SERVER_NAME']); //with http:// and NO trailing slash pls

define('DEFAULT_ROUTE','page');
define('DEFAULT_ACTION','index');


//===============================================
// Debug
//===============================================
ini_set('display_errors','On');
error_reporting(E_ALL);


?>