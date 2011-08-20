<?php
/*****************************************************************
KISSCMS -  http://kisscms.com
A Make Sites (www.makesit.es) production by Makis Tracend
Licensed under the GNU - http://www.gnu.org/licenses/gpl-2.0.txt
*****************************************************************/

//===============================================
// PATHS
//===============================================

// where the app is located
define('APP', realpath("../").'/app/'); //with trailing slash pls

// the location where the SQLite databases will be saved
define('DATA', realpath("../").'/data/');

// the location of the website in relation with the domain root
define('WEB_FOLDER','/');
// alternatively use this if you do not have mod_rewrite enabled
//define('WEB_FOLDER','/index.php/'); 

// full path of where the templates reside
define('TEMPLATES', $_SERVER['DOCUMENT_ROOT'] . WEB_FOLDER . 'templates/'); 


// Optional Attributes

// Optional: you can set the location of your plugins - by default a subdir in the app folder
//define('PLUGINS', APP . 'plugins/');

// find if this is running from localhost
//define("IS_LOCALHOST", (strpos($_SERVER['SERVER_NAME'], "localhost") !== false) );

//if(IS_LOCALHOST){
// include a BASE constant here if this is a clone site
//	define('BASE', APP);
//} else {
// include a BASE constant here if this is a clone site
//	define('BASE', APP);
// the url of your cdn, if you're using one
//	define('CDN', 'http://' . $_SERVER['SERVER_NAME'] . '/'); 
//}


//===============================================
// Start the controller
//===============================================
if (defined("APP") && is_file(APP.'bin/init.php')){ 
	// find the clone file first
	require(APP.'bin/init.php');
} elseif (defined("BASE") && is_file(BASE.'bin/init.php')) {
	// find the core file second
	require(BASE.'bin/init.php');
} else {
	quit("Website Offile");
}

?>