<?php


//===============================================
// KISSCMS Settings 
//===============================================

new Config();

// Register variables
Config::register("main", "default_route", "main");
Config::register("main", "default_action", "index");

Config::register("main", "default_template", "default.php");

Config::register("main", "db_pages", "pages.sqlite");

Config::register("admin", "admin_username", "admin");
Config::register("admin", "admin_password", "admin");

// Definitions
define('DEFAULT_ROUTE', $GLOBALS['config']['main']['default_route']);
define('DEFAULT_ACTION', $GLOBALS['config']['main']['default_action']);

define('DEFAULT_TEMPLATE', $GLOBALS['config']['main']['default_template']);

define('DB_PAGES', $GLOBALS['config']['main']['db_pages']); 


//===============================================
// Debug
//===============================================
ini_set('display_errors','On');
error_reporting(E_ALL);

?>