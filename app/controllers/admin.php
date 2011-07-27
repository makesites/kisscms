<?php

class Admin extends Controller {

	public $data;

	// add call to require login, then pass control back to parent
	function __construct($controller_path,$web_folder,$default_controller,$default_function)  {
		$this->require_login();
		
		// add the config in the data object
		$this->data['config'] = $GLOBALS['config'];
		
		return parent::__construct($controller_path,$web_folder,$default_controller,$default_function);
	}

	function index() {
    	header('Location: '.myUrl('admin/login', true));
	}

	function login() {

	  $login = false;

	  if( isset($_POST['admin_username']) && $_POST['admin_password']){
		$username=trim($_POST['admin_username']);
		$password=$_POST['admin_password'];
		// check for the entered data
		if($username == $GLOBALS['config']['admin']['admin_username'] && $password == $GLOBALS['config']['admin']['admin_password']){
			$login = true;
		}
	  }

	  if($login == true) {
		$_SESSION['admin']="true";
		header('Location: '.myUrl('', true));
		exit();
	  } else {
		// display login form
		//$this->data['body']['admin']= View::do_fetch( getPath('views/admin/login.php'), $this->data);
		$data['view']= getPath('views/admin/login.php');
	  	$this->data['body'][] = $data;

		// display the page
		Template::output($this->data);
	  }

	}

	function logout() {
	  unset($_SESSION['admin']);
	  header('Location: '.myUrl('', true));
	  exit();
	}

	
	function config( $action=null) {

	  if($action == "save" ){
		// placeholder array for the submission
		//$data = array();
		// loop through all the data and reorganise them properly
		foreach($_POST as $k=>$v){
			// get the controller from the field name
			$name = explode("|", $k);
			if(count($name) < 2) continue;
			$table = $name[0];
			$key = $name[1];
			$value = $v;
			// only save the data that have changed
			if( $GLOBALS["config"][$table][$key] != $v ){
				$config = new Config($table);
				//$config->pkname = 'key';
				$config->set('key', $key);
				$config->set('value', $value);
				$config->update();
				$GLOBALS["config"][$table][$key] = $v;
			}
			
		}
		// redirect back to the configuration page
		header('Location: '.myUrl('admin/config', true));
	  } else {
	  	// show the configuration
	  	//$this->data['body']['admin']=View::do_fetch( getPath('views/admin/config.php'),$this->data);
	  	$data['view']= getPath('views/admin/config.php');
	  	$this->data['body'][] = $data;

		// display the page
		Template::output($this->data);
	  }
	}

	/*
	*  CMS Actions
	*/
	function create($path=null) {
		
		$data['status']= $this->data['status']="create";
		$data['path']= ( isset($path) ) ? implode("/", $path) : $_REQUEST['path'];
		$data['tags']= "";
		$data['view']= getPath('views/admin/edit_page.php');
		$data['template']= $this->data['template']= DEFAULT_TEMPLATE;
		//$this->data['admin']=isset($_SESSION['admin']) ? $_SESSION['admin'] : 0;
		
		//$this->data['body']['admin']= View::do_fetch( getPath('views/admin/edit_page.php'), $this->data);
		$this->data['body'][] = $data;
		
		// display the page
		Template::output($this->data);
	}
	
	function edit($id=null) {

		$page=new Page($id);

		// see if we have found a page
		if( $page->get('id') ){
			// store the information of the page
			$data['id'] = $this->data['id'] = $page->get('id');
			$data['title'] = stripslashes( $page->get('title') );
			$data['content'] = stripslashes( $page->get('content') );
			$data['path'] = $this->data['path'] = $page->get('path');
			$data['tags'] = $page->get('tags');
			$data['view'] = getPath('views/admin/edit_page.php');
			$data['status']= $this->data['status']="edit";
			// presentation variables
			$data['template'] = $this->data['template'] = $page->get('template');
		} else {
			$data['status']= $this->data['status']="error";
			$data['view'] = getPath('views/admin/error.php');
		}
			$this->data['body'][] = $data;
		// Now render the output
	  	//$this->data['body']['admin']= View::do_fetch( getPath('views/'.$this->data['view']), $this->data);
		
		// display the page
		Template::output($this->data);
	}

	function update($id=null) {
		
		$validate = $this->validate();
		// see if we have found a page
		if( $validate == true ){
			$this->save($id);
		}
		header('Location: '.myUrl($_REQUEST['path'], true));

	}
	
	function validate() {
		return true;
	}

	function save($id=null) {

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

		if( $id ){
			$page=new Page($id);
			$page->delete();
		} 
		header('Location: '.myUrl('', true));
	}

}
?>