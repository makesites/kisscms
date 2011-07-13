<?php
	function index() {
    	header('Location: '.myUrl('admin/login'));
	}

	/*
	*  CMS Access
	*/
	function login() {
	global $data;
	  $login = false;
	  
	  if( isset($_POST['admin_username']) && $_POST['admin_password']){
		$username=trim($_POST['admin_username']);
		$password=$_POST['admin_password'];
		// check for the entered data
		if($username == $GLOBALS['config']['username'] && $password == $GLOBALS['config']['password']){
			$login = true;
		}
	  }

	  if($login == true) {
		$_SESSION['kisscms_admin']="true";
		header('Location: '.myUrl(''));
		exit();
	  } else {
		// display login form
		cmsHTML();
		$data['body'][]= View::do_fetch( getPath('views/admin/login.php'), $data);
		View::do_dump(TEMPLATE_PATH.'default.php',$data);
	  }

	}

	function logout() {
	  unset($_SESSION['kisscms_admin']);
	  header('Location: '.myUrl(''));
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
	  $data['body'][]=View::do_fetch( getPath('views/admin/config.php'),$data);
	  View::do_dump(TEMPLATE_PATH.'default.php',$data);
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
	  $data['body'][]= View::do_fetch( getPath('views/admin/edit_page.php'), $data);
	  View::do_dump(TEMPLATE_PATH.'default.php',$data);
	}
	
	function edit($id=null) {
	global $data;
	    require_login();
		
		$page=new Page($id);

		// see if we have found a page
		if( $page->get('id') ){
			// store the information of the page
			$data['id'] = $page->get('id');
			$data['title'] = stripslashes( $page->get('title') );
			$data['content'] = stripslashes( $page->get('content') );
			$data['path'] = $page->get('path');
			$data['tags'] = $page->get('tags');
			$data['template'] = $page->get('template');
			// presentation variables
			$data['status']="edit";
			$data['view'] = "admin/edit_page.php";
		} else {
			$data['status']="error";
			$data['view']="admin/error.php";
		}
		// Now render the output
	  cmsHTML();
	  $data['body'][]= View::do_fetch( getPath('views/'.$data['view']), $data);
	  View::do_dump(TEMPLATE_PATH.'default.php',$data);
	}

	function update($id=null) {
	    require_login();
		
		$validate = validate();
		// see if we have found a page
		if( $validate == true ){
			save($id);
		}
		header('Location: '.myUrl($_POST['path']));

	}
	
	function validate() {
		return true;
	}

	function save($id=null) {
	    require_login();
		if( $id ){
			// Update existing page 
			$page=new Page($id);
			$page->set('title', $_POST['title']);
			$page->set('content', $_POST['content']);
			$page->set('tags', $_POST['tags']);
			$page->set('template', "default");
			$page->update();
		} else {
			// Create new page 
			$page=new Page();
			$page->set('title', $_POST['title']);
			$page->set('content', $_POST['content']);
			$page->set('tags', $_POST['tags']);
			$page->set('template', "default");
			$page->set('path', $_POST['path']);
			$page->create();
		}
	}
	
	function delete($id=null) {
	    require_login();
		if( $id ){
			$page=new Page($id);
			$page->delete();
		} 
		header('Location: '.myUrl('main'));
	}

	function cmsHTML() {
	  global $data;
	  // these additional variables add the CMS interface in our website
		$data['cms_styles']= true;
	  if (isset($_SESSION['kisscms_admin'])) {
		$data['cms_topbar']= View::do_fetch( getPath('views/admin/topbar.php'), $data);
	  }
	}

?>