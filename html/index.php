<?php
/*****************************************************************
KISSCMS -  http://kisscms.com
A Make Sites (www.makesit.es) production by Makis Tracend
Licensed under the GNU - http://www.gnu.org/licenses/gpl-2.0.txt
*****************************************************************/

define('CORE_PATH', realpath("../../../").'/etc/app/'); //with trailing slash pls
define('APP_PATH', realpath("../").'/app/'); //with trailing slash pls
define('TEMPLATE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/templates/'); //with trailing slash pls
define('ASSETS_PATH','assets/'); //with trailing slash pls
define('DB_PATH', realpath("../").'/db/data.sqlite'); //with trailing slash pls

define('WEB_FOLDER','/'); //with trailing slash pls
//define('WEB_FOLDER','/index.php/'); //use this if you do not have mod_rewrite enabled

//===============================================
// Globals
//===============================================
$GLOBALS['sitename']='KISSCMS - Lightweight CMS plugged on the KISSMVC Framework';

//===============================================
// Start the controller
//===============================================
require(CORE_PATH.'inc/init.php');
$controller = new Controller( getPath('controllers/'),WEB_FOLDER,'page','index');

?>