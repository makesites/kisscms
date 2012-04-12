<?php
/********************************************************************************
KISSCMS -  http://kisscms.com
A Make Sites production (www.makesites.org) led by Makis Tracend
Dual-licensed under the MIT/X11 license and the GNU General Public License (GPL)
********************************************************************************/


//===============================================
// ENVIRONMENT SETUP
//===============================================

$ENV = (file_exists("../env.json")) ? json_decode( file_get_contents("../env.json") ): array();

// Process enviromental variables (from env.json)
foreach( $ENV as $domain => $properties ){ 
	// check the domain against each set
	if( strpos($_SERVER['SERVER_NAME'], $domain) !== false ){ 
		// available options: base, plugins, cdn, debug (sdk)
		foreach( $properties as $key=>$value ){ 
			if( !empty($value) ) eval("define('".strtoupper($key)."', '$value');");
		}
		// exit if we found a match
		break;
	}	
}


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


//===============================================
// OTHER CONSTANTS
//===============================================

// find if this is running from localhost
define("IS_LOCALHOST", (strpos($_SERVER['SERVER_NAME'], "localhost") !== false) );
// set to true to enable debug mode (where supported) 
if(!defined("DEBUG")) define('DEBUG', false);


//===============================================
// INITIALIZATION
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