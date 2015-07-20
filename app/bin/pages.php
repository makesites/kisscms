<?php

//===============================================
// Pages Initialization
//===============================================

if( class_exists('Page') && method_exists(new Page(),'register')){
	// Register variables
	Page::register("1", "title", "Welcome");
	Page::register("1", "content", "Content Reset... If this was by mistake please restore your database.");
	Page::register("1", "path", "");
	Page::register("1", "tags", "");
	Page::register("1", "template", $GLOBALS['config']['main']['default_template']);
	Page::register("1", "created", time('now') );
	Page::register("1", "updated", time('now') );
}

?>
