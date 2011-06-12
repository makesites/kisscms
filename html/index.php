<?php
/*****************************************************************
KISSCMS -  http://kisscms.com
A Make Sites (www.makesit.es) production by Makis Tracend
Licensed under the GNU - http://www.gnu.org/licenses/gpl-2.0.txt
*****************************************************************/

//===============================================
// Constants
//===============================================

// main constants that define the core/clone model
define('CORE_PATH', realpath("../../../").'/etc/app/'); //with trailing slash pls
define('APP_PATH', realpath("../").'/app/'); //with trailing slash pls

// full path of where the templates reside
define('TEMPLATE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/templates/'); 
// the url of your cdn, if you're using one
define('ASSETS_PATH', 'http://' . $_SERVER['SERVER_NAME'] . '/assets/'); 
// the location of the SQLite database
define('DB_PATH', realpath("../").'/db/data.sqlite');

// the location of the website in relation with the domain root
define('WEB_FOLDER','/');
// alternatively use this if you do not have mod_rewrite enabled
//define('WEB_FOLDER','/index.php/'); 

//===============================================
// Global Variables
//===============================================
$GLOBALS['sitename']='KISSCMS - Lightweight CMS plugged on the KISSMVC Framework';

//===============================================
// Start the controller
//===============================================
require(CORE_PATH.'inc/init.php');
$controller = new Controller( getPath('controllers/'),WEB_FOLDER,'page','index');

?>