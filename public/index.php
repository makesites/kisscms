<?php
/********************************************************************************
KISSCMS -  http://kisscms.com
A Make Sites production (www.makesites.org) led by Makis Tracend
Dual-licensed under the MIT/X11 license and the GNU General Public License (GPL)
********************************************************************************/

//===============================================
// ENVIRONMENT SETUP
//===============================================

// Return message string describing any detected major problem; return FALSE otherwise.
// 
// Purpose: keep work variables in local scope while the configuration file is processed.
function environment_setup() {
	$rv = false;
	$server_settings_found = false;
	$ENV = json_decode( file_get_contents( ( file_exists("../env.json") ) ? "../env.json": "env.json" ) );

	// Process enviromental variables (from env.json)
	foreach( $ENV as $domain => $properties ){ 
		// check the domain against each set
		if( strpos($_SERVER['SERVER_NAME'], $domain) !== false ){ 
			// available options: base, plugins, cdn, debug (sdk)
			foreach( $properties as $key=>$value ){ 
				if( !empty($value) ) eval("define('".strtoupper($key)."', '$value');");
			}
			$server_settings_found = true;
			// exit if we found a match
			break;
		}
	}

	if (!$server_settings_found) {
		$rv = sprintf("Missing environment section for server name '%s'.", $_SERVER['SERVER_NAME']);
	}

	return $rv;
}

//===============================================
// INITIALIZATION
//===============================================

$errmsg = environment_setup();
if (defined("APP") && is_file(APP.'bin/init.php')){ 
	// find the clone file first
	require_once(APP.'bin/init.php');
} elseif (defined("BASE") && is_file(BASE.'bin/init.php')) {
	// find the core file second
	require_once(BASE.'bin/init.php');
} else {
	die("Environment variables not setup properly. Open env.json and edit as needed... " . $errmsg);
}

?>