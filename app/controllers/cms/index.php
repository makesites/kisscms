<?php
	function index() {
    header('Location: '.myUrl('cms/login'));
	}

	/*
	*  CMS Access
	*/
	function login() {
	global $data;
	  $login = false;
	  
	  if( isset($_POST['cms_username']) && $_POST['cms_password']){
		$username=trim($_POST['cms_username']);
		$password=$_POST['cms_password'];
		// check for the entered data
		if($username == $GLOBALS['config']['username'] && $password == $GLOBALS['config']['password']){
			$login = true;
		}
	  }

	  if($login == true) {
		$_SESSION['kisscms_admin']="true";
		header('Location: '.myUrl('main'));
		exit();
	  } else {
		// display login form
		cmsHTML();
		$data['body'][]= viewFetch('cms/login.php', $data);
		viewDump('layout.php',$data);
	  }

	}

	function logout() {
	  unset($_SESSION['kisscms_admin']);
	  header('Location: '.myUrl('main'));
	  exit();
	}

	
	function config( $action=null) {
	global $data;
	  require_login();
	  cmsHTML();
	  
	  if($action == "save"){
	    $dbh = getdbh();
		$s='';
		foreach($_POST as $k=>$v){
			$sql = 'UPDATE "config" SET "value"="' . $v . '" WHERE "name"="' . $k . '"';
			$results = $dbh->query($sql);
	    //echo $sql . "<br />\n";
		}
		header('Location: '.myUrl('main'));
	  } else {
	  // show the configuration
	  $data['body'][]=viewFetch('cms/config.php',$data);
	  viewDump('layout.php',$data);
	  }
	}

	/*
	*  CMS Actions
	*/
	function create($path=null) {
	global $data;
	  require_login();
	  
	  $data['status']="create";
	  $data['path']= ( isset($path) ) ? $path : $_POST['path'];
	  cmsHTML();
	  $data['body'][]= viewFetch('cms/edit_page.php', $data);
	  viewDump('layout.php',$data);
	}
	
	function edit($id=null) {
	global $data;
	    require_login();
		
		$page=new CMS($id);

		// see if we have found a page
		if( $page->get('id') ){
			// store the information of the page
			$data['id'] = $page->get('id');
			$data['title'] = stripslashes( $page->get('title') );
			$data['content'] = stripslashes( $page->get('content') );
			$data['path'] = $page->get('path');
			// presentation variables
			$data['status']="edit";
			$data['fragment'] = "cms/edit_page.php";
		} else {
			$data['status']="error";
			$data['fragment']="cms/error.php";
		}
		// Now render the output
	  cmsHTML();
	  $data['body'][]= viewFetch($data['fragment'], $data);
	  viewDump('layout.php',$data);
	}

	function update($id=null) {
	    require_login();
		
		$validate = validate();
		// see if we have found a page
		if( $validate == true ){
			save($id);
		}
		header('Location: '.myUrl('main/' . $_POST['path']));

	}
	
	function validate() {
		return true;
	}

	function save($id=null) {
	    require_login();
		if( $id ){
			// Update existing page 
			$page=new CMS($id);
			$page->set('title', $_POST['title']);
			$page->set('content', $_POST['content']);
			$page->update();
		} else {
			// Create new page 
			$page=new CMS();
			$page->set('title', $_POST['title']);
			$page->set('content', $_POST['content']);
			$page->set('path', $_POST['path']);
			$page->create();
		}
	}
	
	function delete($id=null) {
	    require_login();
		if( $id ){
			$page=new CMS($id);
			$page->delete();
		} 
		header('Location: '.myUrl('main'));
	}

	function cmsHTML() {
	  global $data;
	  // these additional variables add the CMS interface in our website
		$data['cms_styles']= true;
	  if (isset($_SESSION['kisscms_admin'])) {
		$data['cms_topbar']= viewFetch('cms/topbar.php', $data);
	  }
	}

?>