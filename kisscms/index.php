<?php
/*****************************************************************
Copyright (c) 2008 
Eric Koh <erickoh75@gmail.com> http://kissmvc.com

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
*****************************************************************/
//===============================================
// Debug
//===============================================
ini_set('display_errors','On');
error_reporting(E_ALL);

//===============================================
// mod_rewrite
//===============================================
//Please configure via .htaccess or httpd.conf

//===============================================
// KISSMVC Settings (please configure)
//===============================================
define('APP_PATH','app/'); //with trailing slash pls
define('ASSETS_PATH','assets/'); //with trailing slash pls
define('WEB_DOMAIN','http://localhost'); //with http:// and NO trailing slash pls
//define('WEB_FOLDER','/'); //with trailing slash pls
define('WEB_FOLDER','/kisscms/index.php/'); //use this if you do not have mod_rewrite enabled

define('DEFAULT_ROUTE','main');
define('DEFAULT_ACTION','index');

define('DATABASE_FILE','data/db/kisscms.sqlite');

//===============================================
// Includes
//===============================================
require('kissmvc.php');
require(APP_PATH.'inc/common.php');
require(APP_PATH.'inc/language.php');
require(APP_PATH.'inc/modules.php');
require_once(APP_PATH.'models/cms.php');

//===============================================
// Session
//===============================================
session_start();

//===============================================
// Globals
//===============================================
getGlobals();

//===============================================
// Start the controller
//===============================================
requestRouter();
