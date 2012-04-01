<?php
/********************************************************************************
KISSCMS -  http://kisscms.com
A Make Sites production (www.makesites.org) led by Makis Tracend
Dual-licensed under the MIT/X11 license and the GNU General Public License (GPL)
********************************************************************************/


//===============================================
// ENVIRONMENT SETUP
//===============================================
$env = json_decode( file_get_contents("../env.json") );


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

// Process enviromental variables (from env.json)
foreach( $env as $domain => $setup ){ 
	// check the domain against each set
	if( strpos($_SERVER['SERVER_NAME'], $domain) !== false ){ 
		// available options: base, plugins, cdn, debug
		// - include a BASE constant here if this is a clone site
		if( !empty($setup->base) ) 		eval("define('BASE', 		'$setup->base');");
		// - you can set the location of your plugins - by default a subdir in the app folder
		if( !empty($setup->plugins) ) 	eval("define('PLUGINS', 	'$setup->plugins');");
		if( !empty($setup->cdn) ) 		eval("define('CDN', 		'$setup->cdn');");
		if( !empty($setup->debug) ) 	eval("define('DEBUG', 		'$setup->debug');");
		break;
	}
		var_dump(DEBUG);
	
}

// Other Constants
// - find if this is running from localhost
define("IS_LOCALHOST", (strpos($_SERVER['SERVER_NAME'], "localhost") !== false) );
// - set to true to enable debug mode (where supported) 
if(!defined("DEBUG")) define('DEBUG', false);


//===============================================
// Start the controller
//===============================================
if (defined("APP") && is_file(APP.'bin/init.php')){ 
	// find the clone file first
	require_once(APP.'bin/init.php');
} elseif (defined("BASE") && is_file(BASE.'bin/init.php')) {
	// find the core file second
	require_once(BASE.'bin/init.php');
} else {
	die("Environment varialbes not setup properly. Open env.json and edit as needed...");
}

?>