<?php

//===============================================
// Includes
//===============================================
// follows this order: 
//- libs,helpers,models in the app folder
//- libs,helpers,models in the base folder
//- files in this dir
//- plugins init.php in the app/base folder
//- plugins init.php in the plugins folder

requireAll( "lib" );
// by default load the mvc.php first
requireAll( "helpers", null, array("mvc.php") );
requireAll( "models" );
// include config and other initiators
requireAll( dirname(__FILE__), array("init.php") );
// include plugins
requireOnly( "plugins", array("bin/init.php") );
if( defined("PLUGINS") ){
	requireOnly( PLUGINS, array("bin/init.php"));
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
function requireAll($folder='', $exclude=array(), $priority=array()){
	
	// find all the files in the APP, BASE and the folder
	$files = $app = $base = $exception = $priorities = array();
	
	// all the files that have a full path
	$files = glob("$folder/*",GLOB_BRACE);
	if(!$files) $files = array();

	// all the files in the exception list
	if( is_array($exclude) ){
		foreach($exclude as $file){ 
			$exception = glob("{*}$folder/$file",GLOB_BRACE);
			if(!$exception) $exception = array();
			if( defined("APP") ){ 
				$search = glob(APP."$folder/$file",GLOB_BRACE);
				$exception =  array_merge( $exception, (array)$search );
			}
			if( defined("BASE") ){ 
				$search = glob(BASE."$folder/$file",GLOB_BRACE);
				$exception = array_merge( $exception, (array)$search );
			}
		}
	}
	// all the files in the priority list
	if( is_array($priority) ){
		foreach($priority as $file){ 
			$priorities = (array)glob("$folder/$file",GLOB_BRACE);
			if(!$priorities) $priorities = array();
			if( defined("APP") ){ 
				$search = glob(APP."$folder/$file",GLOB_BRACE);
				$priorities =  array_merge( $priorities, (array)$search );
			}
			if( defined("BASE") ){ 
				$search = glob(BASE."$folder/$file",GLOB_BRACE);
				$priorities = array_merge( $priorities, (array)$search );
			}
		}
	}

	
	// look into the app folder
	if( defined("APP") ){
		$app = glob(APP."$folder/*",GLOB_BRACE);
		if(!$app) $app = array();
	}
	
	// look into the base folder
	if( defined("BASE") ){
		$base = glob(BASE."$folder/*",GLOB_BRACE);
		if(!$base) $base = array();
		// compare the files and exclude all the APP overrides 
		foreach($base as $key=>$file){
			// remove the path
			$target = substr($file,strlen(BASE));
			// see if the target exists in the app folder
			if(file_exists(APP.$target)){
				// remove it from the array
				$base[$key] = null;
			}
		}
	}
	
	
	// merge all the arrays together
	$files = array_merge( $files, $base, $app );

	// remove all the files in the exclude list
	foreach($exception as $key=>$delete){
		if(in_array($delete, $files)){
			// remove it from the array
			$files[$key] = null;
		}
	}
	
	// require the $priority files first
	foreach($priorities as $key=>$file){ 
		if(in_array($file, $files)){
			// include it first
			if( is_file( $file )) require_once( $file );
		}
	}
	
	// require all the rest of the files
	foreach($files as $file){ 
		if( is_file( $file )) require_once( $file );
	}
}

function requireOnly($folder='', $only=array() ){
	
	// find all the files in the APP, BASE and the folder
	$files = $app = $base = array();
	
	// all the files that have a full path
	$files = glob("$folder/*",GLOB_BRACE);
	if(!$files) $files = array();

	foreach($only as $file){
		
		if( defined("APP") ){
			$search = glob(APP."$folder/*/$file",GLOB_BRACE);
			$app = array_merge( $app, (array)$search );
			if(!$app) $app = array();
		}	
		if( defined("BASE") ){
			$search = glob(BASE."$folder/*/$file",GLOB_BRACE);
			$base = array_merge( $base, (array)$search );
			if(!$base) $base = array();
			// compare the files and exclude all the APP overrides 
			foreach($base as $key=>$file){
				// remove the path
				$target = substr($file,strlen(BASE));
				// see if the target exists in the app folder
				if(file_exists(APP.$target)){
					// remove it from the array
					$base[$key] = null;
				}
			}
		}
		
		// merge all the arrays together
		$files = array_merge( $files, $base, $app );

	}
	
	// require all the files found
	foreach($files as $file){
		if( is_file( $file )) require_once( $file );
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