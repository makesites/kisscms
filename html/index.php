<?php
/*****************************************************************
KISSCMS -  http://kisscms.com
A Make Sites (www.makesit.es) production by Makis Tracend
Licensed under the GNU - http://www.gnu.org/licenses/gpl-2.0.txt
*****************************************************************/

//===============================================
// Constants
//===============================================

// where the app is located - include a BASE constant here if this is a clone site
define('APP', realpath("../").'/app/'); //with trailing slash pls
// the location of the website in relation with the domain root

define('WEB_FOLDER','/');
// alternatively use this if you do not have mod_rewrite enabled
//define('WEB_FOLDER','/index.php/'); 

// the location where the SQLite databases will be saved
define('DATA', realpath("../").'/data/');

// full path of where the templates reside
define('TEMPLATES', $_SERVER['DOCUMENT_ROOT'] . WEB_FOLDER . '/templates/'); 

// the url of your cdn, if you're using one
define('ASSETS', 'http://' . $_SERVER['SERVER_NAME'] . '/assets/'); 


//===============================================
// Global Variables
//===============================================
$GLOBALS['sitename']='KISSCMS - Lightweight CMS based on the KISSMVC Framework';


//===============================================
// Start the controller
//===============================================
if (defined("BASE")){ 
	// find the clone file first
	require(BASE.'bin/init.php');
} elseif (defined("APP")) {
	// find the core file second
	require(APP.'bin/init.php');
} else {
	quit("Please define the app path in your index file");
}

$controller = new Controller( getPath('controllers/'),WEB_FOLDER,'page','index');

?>