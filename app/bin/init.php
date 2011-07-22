<?php

//===============================================
// Includes
//===============================================
// follows this order: 
//- libs,helpers in the app/base folder
//- models in the app/base folder
//- files in this dir
//- plugins init.php in the app/base folder

if( defined("APP") ){
	requireAll( APP."lib/" );
	requireAll( APP."helpers/" );
	requireAll( APP."models/" );
}
if( defined("BASE") ){
	requireAll( BASE."lib/" );
	requireAll( BASE."helpers/" );
	requireAll( BASE."models/" );
}

requireAll( dirname(__FILE__)."/", null, array("init.php") );

if( defined("APP") ){
	requireAll( APP."plugins/", array("/bin/init.php"));
}
if( defined("BASE") ){
	requireAll( BASE."plugins/", array("/bin/init.php"));
}
if( defined("PLUGINS") ){
	requireAll( PLUGINS, array("/bin/init.php"));
}



//===============================================
// Session
//===============================================
session_start();


//===============================================
// Routes
//===============================================
// first check if this is a "static" asset
$url = parse_url( $_SERVER['REQUEST_URI'] );
if ($output = isStatic($url['path']) ) {
	echo getFile( $output );
	exit;
} else {
	$controller = findController($url['path']);
	
}
//requestParserCustom($controller,$action,$params);

//===============================================
// Including Files
//===============================================
function requireAll($folder='', $only=array(), $exclude=array()) {
if ($handle = opendir($folder)) {
    
	// include everything unless explicitly specified
	while (false !== ($file = readdir($handle))) {
		if ($file == '.' || $file == '..') { 
		  continue; 
		} 	
		if( count( $only ) > 0 ){ 
			// include only the files in the $only array
			foreach( $only as $target ){
				if(file_exists($folder.$file.$target)){
					require_once( $folder.$file.$target );
				}
			}
		} elseif( count( $exclude ) > 0 ){
			// exclude all the files in the $exclude array
			foreach( $exclude as $target ){
				if ($file != $target && file_exists($folder.$file)) {				
		  			require_once( $folder.$file );
				}
			}
		} else {
			if(file_exists($folder.$file)){
				require_once( $folder.$file );
			}	
		}
	}
	
    closedir($handle);
}  
}


//===============================================
// Uncaught Exception Handling
//===============================================s
set_exception_handler('uncaught_exception_handler');

function uncaught_exception_handler($e) {
  ob_end_clean(); //dump out remaining buffered text
  $vars['message']=$e;
  die(View::do_fetch( getPath('views/errors/exception_uncaught.php'),$vars));
}

function custom_error($msg='') {
  $vars['msg']=$msg;
  die(View::do_fetch( getPath('views/errors/custom_error.php'),$vars));
}


//===============================================
// Srart the controller
//===============================================s

$output = new $controller( 'controllers/',WEB_FOLDER,'main','index');

?>