<?php
/*****************************************************************
KISSCMS -  http://kisscms.com
A Make Sites (www.makesit.es) production by Makis Tracend
Licensed under the GNU - http://www.gnu.org/licenses/gpl-2.0.txt
*****************************************************************/

//===============================================
// Constants
//===============================================

// main constants that define the core/clone model - include a BASE constant to where the core files live
define('APP', realpath("../").'/app/'); //with trailing slash pls

// full path of where the templates reside
define('TEMPLATES', $_SERVER['DOCUMENT_ROOT'] . '/templates/'); 
// the url of your cdn, if you're using one
define('ASSETS', 'http://' . $_SERVER['SERVER_NAME'] . '/assets/'); 
// the location of the SQLite database
define('DATA', realpath("../").'/data/');

// the location of the website in relation with the domain root
define('WEB_FOLDER','/');
// alternatively use this if you do not have mod_rewrite enabled
//define('WEB_FOLDER','/index.php/'); 

//===============================================
// Global Variables
//===============================================
$GLOBALS['sitename']='KISSCMS - Lightweight CMS based on the KISSMVC Framework';

//===============================================
// Start the controller
//===============================================
if (defined("BASE")){ 
	// find the clone file first
	require(BASE.'inc/init.php');
} elseif (defined("APP")) {
	// find the core file second
	require(APP.'inc/init.php');
} else {
	quit("Please define the app path in your index file");
}

$controller = new Controller( getPath('controllers/'),WEB_FOLDER,'page','index');

?>