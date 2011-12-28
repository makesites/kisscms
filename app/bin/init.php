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

lookUpDirs();

requireAll( "lib" );
// by default load the mvc.php first - which should only be one!
requireAll( "helpers", false, array("mvc.php") );
// load all the models (dependent on helpers)
requireAll( "models" );
// load all initializations
requireOnly( "bin", array("init.php") );
// load config and other initiators (dependent on helpers)
requireAll( "bin", array("init.php") );


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



// Lookup available dirs in our environment
function lookUpDirs(){
	
	if( defined("APP") ){
		// check if there is a directory in that location
		if( is_dir( APP ) ){ 
			// do nothing atm, this condition will evaluate just true in MOST cases
		} else {
			// create it if not
			mkdir(APP, 0775);
		}
	}
	
	// in the future create a global array of dirs here to replace the "if app" & "if base" conditions
}


//===============================================
// Including Files
//===============================================
function requireAll($folder='', $exclude=array(), $priority=array()){
	
	// find all the files in the APP, BASE and the folder
	$files = $app = $base = $plugins = $exception = $priorities = array();
	
	// all the files that have a full path
	$files = glob("$folder/*",GLOB_BRACE);
	if(!$files) $files = array();

	// all the files in the exception list
	if( is_array($exclude) ){
		foreach($exclude as $file){ 
			$exception = glob("$folder/$file",GLOB_BRACE);
			if(!$exception) $exception = array();
			if( defined("APP") ){ 
				$search = glob(APP."$folder/$file",GLOB_BRACE);
				if($search) $exception =  array_merge( $exception, (array)$search );
				// check the plugins subfolder
				$search = glob(APP."plugins/*/$folder/$file",GLOB_BRACE);
				if($search) $exception =  array_merge( $exception, (array)$search );
			}
			if( defined("BASE") ){ 
				$search = glob(BASE."$folder/$file",GLOB_BRACE);
				if($search) $exception = array_merge( $exception, (array)$search );
				// check the plugins subfolder
				$search = glob(BASE."plugins/*/$folder/$file",GLOB_BRACE);
				if($search) $exception =  array_merge( $exception, (array)$search );
			}
			// check in the plugins directory
			if( defined("PLUGINS")){
				$search = glob(PLUGINS."*/$folder/$file",GLOB_BRACE);
				if($search) $exception = array_merge( $exception, (array)$search );

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
				if($search) $priorities =  array_merge( $priorities, (array)$search );
				// check the plugins subfolder
				$search = glob(APP."plugins/*/$folder/$file",GLOB_BRACE);
				if($search) $priorities =  array_merge( $priorities, (array)$search );
			}
			if( defined("BASE") ){ 
				$search = glob(BASE."$folder/$file",GLOB_BRACE);
				if($search) $priorities = array_merge( $priorities, (array)$search );
				// check the plugins subfolder
				$search = glob(BASE."plugins/*/$folder/$file",GLOB_BRACE);
				if($search) $priorities =  array_merge( $priorities, (array)$search );
			}
			// check in the plugins directory
			if( defined("PLUGINS")){
				$search = glob(PLUGINS."*/$folder/$file",GLOB_BRACE);
				if($search) $priorities =  array_merge( $priorities, (array)$search );

			}
		}
	}

	
	// look into the app folder
	if( defined("APP") ){
		$search = glob(APP."$folder/*",GLOB_BRACE);
		if($search) $app = array_merge( $app, (array)$search );
		// check the plugins subfolder
		$search = glob(APP."plugins/*/$folder/*",GLOB_BRACE);
		if($search) $app =  array_merge( $app, (array)$search );
	}

	// look into the base folder
	if( defined("BASE") ){
		$search = glob(BASE."$folder/*",GLOB_BRACE);
		if($search) $base =  array_merge( $base, (array)$search );
		// check the plugins subfolder
		$search = glob(BASE."plugins/*/$folder/*",GLOB_BRACE);
		if($search) $base =  array_merge( $base, (array)$search );
		
		// compare the files and exclude all the APP overrides 
		foreach($base as $key=>$file){
			// remove the path
			$target = substr($file,strlen(BASE));
			// see if the target exists in the app folder
			if(file_exists(APP.$target)){
				// remove it from the array
				unset($base[$key]);
			}
		}
	}
	// look into the plugins folder
	if( defined("PLUGINS") ){
		$plugins = glob(PLUGINS."*/$folder/*",GLOB_BRACE);
		if(!$plugins) $plugins = array();
	}
	
	
	// merge all the arrays together
	$files = array_merge( $files, $base, $app, $plugins );

	// remove all the files in the exclude list
	foreach($exception as $key=>$file){
	
		if(in_array($file, $files)){
			// remove it from the array
			unset($files[array_search($file, $files)]);
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
	$files = $app = $base = $plugins = array();
	
	foreach($only as $file){
		
		// all the files that have a full path
		$files = glob("$folder/$file",GLOB_BRACE);
		if(!$files) $files = array();

		if( defined("APP") ){
			$search = glob(APP."$folder/$file",GLOB_BRACE);
			if($search) $app = array_merge( $app, (array)$search );
			// check the plugins subfolder
			$search = glob(APP."plugins/*/$folder/$file",GLOB_BRACE);
			if($search) $app = array_merge( $app, (array)$search );
		}	
		if( defined("BASE") ){
			$search = glob(BASE."$folder/$file",GLOB_BRACE);
			if($search) $base = array_merge( $base, (array)$search );
			// check the plugins subfolder
			$search = glob(BASE."plugins/*/$folder/$file",GLOB_BRACE);
			if($search) $base = array_merge( $base, (array)$search );

			// compare the files and exclude all the APP overrides 
			foreach($base as $key=>$file){
				// remove the path
				$target = substr($file,strlen(BASE));
				// see if the target exists in the app folder
				if(file_exists(APP.$target)){
					// remove it from the array
					unset($base[$key]);
				}
			}
		}
		if( defined("PLUGINS") ){
			$search = glob(PLUGINS."*/$folder/$file",GLOB_BRACE);
			if($search) $plugins = array_merge( $plugins, (array)$search );
		}
		// merge all the arrays together
		$files = array_merge( $files, $base, $app, $plugins );

	}
	
	// require all the files found
	foreach($files as $file){
		// finally exclude the file that is running
		if( is_file( $file ) && $file != __FILE__ ) require_once( $file );
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

$output = new $controller( 'controllers/', WEB_FOLDER, DEFAULT_ROUTE, DEFAULT_ACTION);

?>