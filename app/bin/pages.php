<?php

//===============================================
// Pages Initialization 
//===============================================

// Register variables
Page::register("1", "title", "Welcome");
Page::register("1", "content", "Content Reset... If this was by mistake please restore your database.");
Page::register("1", "path", "/");
Page::register("1", "template", $GLOBALS['config']['main']['default_template']);

?>