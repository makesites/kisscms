<?php

	function index($path='') {
	  global $data;

	  $data['path'] = $path;

	  // evaluate the path to get the page we are looking for 
	  parsePath( $data );

	  // get the page details stored in the database
	  requestPage( $data );
	
	  // add aditional information if this is the admin
	  checkLogin();

	  // load the fragment for the page content
	  $data['body'][]= viewFetch($data['fragment'], $data);
	  View::do_dump(VIEW_PATH.'layouts/mainlayout.php',$data);

	   $data['pagename']='Welcome to KISSMVC';
	   $data['body'][]=View::do_fetch(VIEW_PATH.'main/index.php');
		
	}
	
	function parsePath( &$data ) {
		// check if we have a trailing slash (and remove it) 
		$data['path'] = ( substr($data['path'], -1) == "/" ) ? substr($data['path'], 0, -1) : $data['path'];
		// create a fragment name out of the path
		$data['fragment'] =  str_replace("/", "_", $data['path']);
		$fragment_file=APP_PATH.'views/fragments/'.$data['fragment'].'.php';
		// use the default fragment if the custom file is not available
		$data['fragment'] = ( file_exists($fragment_file) ) ? 'fragments/'.$data['fragment'].'.php' : 'fragments/main.php';
	}

	function requestPage( &$data ) {

		$page=new CMS();
		$page->get_page_from_path($data['path']);

		// see if we have found a page
		if( $page->get('id') ){
			// store the information of the page
			$data['id'] = $page->get('id');
			$data['title'] = stripslashes( $page->get('title') );
			$data['content'] = stripslashes( $page->get('content') );
		} else {
			// forward to create a new page
			$data['status']="new";
			$data['fragment']="cms/confirm_new.php";
		}

	}

	function checkLogin() {
	  global $data;
	  // check if admin is logged in and apply interface updates
	  if (isset($_SESSION['kisscms_admin'])) {
		$data['cms_styles']= true;
		$data['cms_topbar']= viewFetch('cms/topbar.php', $data);
	  }
	}

?>