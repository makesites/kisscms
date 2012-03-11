<?php

class Admin extends Controller {

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
		header('Location: '.myUrl());
		exit();
	  } else {
		// display login form
		//$this->data['body']['admin']= View::do_fetch( getPath('views/admin/login.php'), $this->data);
		$data['view']= getPath('views/admin/login.php');
	  	$this->data['body'][] = $data;
		$this->data['template']= ADMIN_TEMPLATE;
		
		// display the page
		Template::output($this->data);
	  }

	}

	function logout() {
	  unset($_SESSION['admin']);
	  header('Location: '.myUrl());
	  exit();
	}

	
	function config( $params=null) {

	  if($params['action'] == "save" ){
		// remove the action param
		unset($params['action']); 
		// loop through all the other data and reorganise them properly
		foreach($params as $k=>$v){
			// get the controller from the field name
			$name = explode("|", $k);
			if(count($name) < 2) continue;
			$table = $name[0];
			$key = $name[1];
			$value = $v;
			// only save the data that has changed
			if( $GLOBALS["config"][$table][$key] != $value ){
				$config = new Config(0, $table);
				//$config->pkname = 'key';
				$config->set('key', $key);
				$config->set('value', $value);
				$config->update();
				// update memory
				$GLOBALS["config"][$table][$key] = $value;
			}
			
		}
		// generate the humans.txt file
		$humans = $this->humansText();
		
		// redirect back to the configuration page
		header('Location: '.myUrl('admin/config'));
	  } else {
	  	// show the configuration
	  	//$this->data['body']['admin']=View::do_fetch( getPath('views/admin/config.php'),$this->data);
	  	$data['view']= getPath('views/admin/config.php');
		$this->data['status']= "config";
	  	$this->data['body'][] = $data;
		$this->data['template']= ADMIN_TEMPLATE;
		
		// display the page
		Template::output($this->data);
	  }
	}

	/*
	*  CMS Actions
	*/
	function create($params=false) {
		
		$data['status']= $this->data['status']="create";
		$data['path']= ( array_key_exists("path", $params) ) ? $params["path"] : clean($_REQUEST['path']);
		$data['tags']= "";
		$data['view']= getPath('views/admin/edit_page.php');
		$data['template']= DEFAULT_TEMPLATE;
		//$this->data['admin']=isset($_SESSION['admin']) ? $_SESSION['admin'] : 0;
		
		//$this->data['body']['admin']= View::do_fetch( getPath('views/admin/edit_page.php'), $this->data);
		$this->data['body'][] = $data;
		$this->data['template']= ADMIN_TEMPLATE;
		
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
			$data['template'] = $page->get('template');
		} else {
			$data['status']= $this->data['status']="error";
			$data['view'] = getPath('views/admin/error.php');
		}
		
		// Now render the output
	  	$this->data['body'][] = $data;
		$this->data['template']= ADMIN_TEMPLATE;
		
		// display the page
		Template::output($this->data);
	}

	function update($params=null) {
		
		$validate = $this->validate();
		// see if we have found a page
		if( $validate == true ){
			$this->save($params);
		}
		header('Location: '.myUrl($_REQUEST['path']));

	}
	
	function validate() {
		// insert proper validation HERE
		return true;
	}

	function save($params=null) {

		if( array_key_exists("id", $params) ){
			// Update existing page 
			$page=new Page($params['id']);
			$page->set('title', $params['title']);
			$page->set('content', $params['content']);
			$page->set('tags', $params['tags']);
			$page->set('template', $params['template']);
			$page->update();
		} else {
			var_dump( $_POST['path'] );
			// Create new page 
			$page=new Page();
			$page->set('title', $params['title']);
			$page->set('content', $params['content']);
			$page->set('tags', $params['tags']);
			$page->set('template', $params['template']);
			$page->set('path', $params['path']);
			$page->create();
		}
		
		// Generate sitemap
		$sitemap = new Sitemap();
		
	}
	
	function delete($id=null) {

		if( $id ){
			$page=new Page($id);
			$page->delete();
		} 
		header('Location: '.myUrl());
	}

	function humansText(){
		//get config
		$data['config'] = $GLOBALS['config'];
		// load tempalate
		$output = View::do_fetch( getPath("views/admin/humans.php"), $data);
		// write file
		writeFile(APP.'public/humans.txt', $output, 'w');
				
	}
}
?>