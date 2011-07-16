<?php
	
	function index($params=array()) {
	  global $data;

	  $data['path'] = (array_key_exists('path', $params)) ? $params['path'] : "";
	  
	  // evaluate the path to get the page we are looking for 
	  parsePath( $data );

	  // get the page details stored in the database
	  requestPage( $data );
	
	  // add aditional information if this is the admin
	  checkLogin();
	  
	  $data['admin']=isset($_SESSION['admin']) ? $_SESSION['admin'] : 0;

	  // load the view for the page content
	  $data['body'][]= View::do_fetch($data['view'], $data);
	  $data['head'] = array();
	  $data['aside'] = array();
	  View::do_dump(TEMPLATES.'default.php',$data);

	   $data['pagename']='Welcome to KISSCMS';
	   $data['body'][]=View::do_fetch(  getPath('views/main.php') );
		
	}
	
	function parsePath( &$data ) {
		// check if we have a trailing slash (and remove it) 
		$data['path'] = ( substr($data['path'], -1) == "/" ) ? substr($data['path'], 0, -1) : $data['path'];
		// create a view name out of the path
		$data['view'] =  str_replace("/", "_", $data['path']);
		$view_file=  getPath('views/' . $data['view'].'.php');
		// use the default view if the custom file is not available
		$data['view'] = ( file_exists($view_file) ) ? $view_file :  getPath('views/main.php');
	}

	function requestPage( &$data ) {

		$page=new Page();
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
			$data['view']= getPath('views/admin/confirm_new.php');
		}

	}

	function checkLogin() {
	  global $data;
	  // check if admin is logged in and apply interface updates
	  if (isset($_SESSION['admin'])) {
		$data['cms_styles']= true;
		$data['cms_topbar']= View::do_fetch(  getPath('views/admin/topbar.php'), $data);
	  } else {
		$data['cms_styles']= false;  
	  }
	}

?>