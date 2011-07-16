<?php

class Admin extends Controller {

	public $data;
	
	function index() {
    	header('Location: '.getURL('admin/login', true));
	}

	/*
	*  CMS Access
	*/
	function login() {

	  $login = false;

	  if( isset($_POST['admin_username']) && $_POST['admin_password']){
		$username=trim($_POST['admin_username']);
		$password=$_POST['admin_password'];
		// check for the entered data
		if($username == $GLOBALS['admin']['username'] && $password == $GLOBALS['admin']['password']){
			$login = true;
		}
	  }

	  if($login == true) {
		$_SESSION['admin']="true";
		header('Location: '.getURL('', true));
		exit();
	  } else {
		// display login form
		$this->cmsHTML();
		$this->data['body'][]= View::do_fetch( getPath('views/admin/login.php'), $this->data);
		View::do_dump(TEMPLATES.'default.php',$this->data);
	  }

	}

	function logout() {
	  unset($_SESSION['admin']);
	  header('Location: '.getURL('', true));
	  exit();
	}

	
	function config( $action=null) {

	  require_login();
	  $this->cmsHTML();
	  
	  if($action == "save" && $GLOBALS['db_pages']){

		$dbh = $GLOBALS['db_pages'];
		$s='';
		foreach($_POST as $k=>$v){
			$sql = 'UPDATE "config" SET "value"="' . $v . '" WHERE "name"="' . $k . '"';
			$results = $dbh->query($sql);
		//echo $sql . "<br />\n";
		}
		header('Location: '.getURL('main', true));
	  } else {
	  // show the configuration
	  $this->data['body'][]=View::do_fetch( getPath('views/admin/config.php'),$this->data);
	  View::do_dump(TEMPLATES.'default.php',$this->data);
	  }
	}

	/*
	*  CMS Actions
	*/
	function create($path=null) {

	  require_login();
	  
	  $this->data['status']="create";
	  $this->data['path']= ( isset($path) ) ? $path : $_POST['path'];
	  $this->cmsHTML();
	  $this->data['body'][]= View::do_fetch( getPath('views/admin/edit_page.php'), $this->data);
	  View::do_dump(TEMPLATES.'default.php',$this->data);
	}
	
	function edit($id=null) {

	    require_login();
		
		$page=new Page($id);

		// see if we have found a page
		if( $page->get('id') ){
			// store the information of the page
			$this->data['id'] = $page->get('id');
			$this->data['title'] = stripslashes( $page->get('title') );
			$this->data['content'] = stripslashes( $page->get('content') );
			$this->data['path'] = $page->get('path');
			$this->data['tags'] = $page->get('tags');
			$this->data['template'] = $page->get('template');
			// presentation variables
			$this->data['status']="edit";
			$this->data['view'] = "admin/edit_page.php";
		} else {
			$this->data['status']="error";
			$this->data['view']="admin/error.php";
		}
		// Now render the output
	  $this->cmsHTML();
	  $this->data['admin']=isset($_SESSION['admin']) ? $_SESSION['admin'] : 0;
	  $this->data['body'][]= View::do_fetch( getPath('views/'.$this->data['view']), $this->data);
	  $this->data['head'] = array();
	  $this->data['aside'] = array();
	  View::do_dump(TEMPLATES.$this->data['template'],$this->data);
	}

	function update($id=null) {
	    require_login();
		
		$validate = $this->validate();
		// see if we have found a page
		if( $validate == true ){
			$this->save($id);
		}
		header('Location: '.getURL($_POST['path'], true));

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
			$page->set('template', $_POST['template']);
			$page->update();
		} else {
			// Create new page 
			$page=new Page();
			$page->set('title', $_POST['title']);
			$page->set('content', $_POST['content']);
			$page->set('tags', $_POST['tags']);
			$page->set('template', $_POST['template']);
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
		header('Location: '.getURL('', true));
	}

	function cmsHTML() {

	  // these additional variables add the CMS interface in our website
		$this->data['cms_styles']= true;
	  if (isset($_SESSION['kisscms_admin'])) {
		$this->data['cms_topbar']= View::do_fetch( getPath('views/admin/topbar.php'), $this->data);
	  }
	}

}
?>