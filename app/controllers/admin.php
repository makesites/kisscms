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
		header('Location: '.url('admin/login', true));
	}

	function login() {

		$login = false;
		// configuration values;
		$db_password = $GLOBALS['config']['admin']['admin_password'];
		$db_username = $GLOBALS['config']['admin']['admin_username'];
		// check user input
		if( isset($_POST['admin_username']) && $_POST['admin_password']){
			$username=trim($_POST['admin_username']);
			$password=crypt($_POST['admin_password'], $db_password);
			// check for the entered data
			// #25 - leaving legacy password check for backwards compatibility (to be deprecated)
			if( $username == $db_username && ( $password == $db_password || $_POST['admin_password'] == $db_password ) ){
				$login = true;
			}
		}

	  if($login == true) {
		$_SESSION['admin']="true";
		header('Location: '.url());
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
	  header('Location: '.url());
	  exit();
	}


	function config( $params=array() ) {
	  // if saving...
	  if( $_SERVER['REQUEST_METHOD'] == "POST" ){

		// loop through all the other data and reorganise them properly
		foreach($params as $k=>$v){
			// exit if the values is empty (but not false)?
			if( empty($v) && $v != "0" ) continue;
			// get the controller from the field name
			$name = explode("|", $k);
			if(count($name) < 2) continue;
			$table = $name[0];
			$key = $name[1];
			//#25 - encrupting 'password' fields
			$value = ( $key== "admin_password" ) ? crypt( $v ) : $v;
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
		header('Location: '.url('admin/config'));

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
		$data['path']= ( is_array($params) && array_key_exists("path", $params) ) ? $params["path"] : clean($_REQUEST['path']);
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

	function update( $params=array() ) {

		$validate = $this->validate();
		// see if we have found a page
		if( $validate == true ){
			$this->save($params);
		}
		header('Location: '.url($_REQUEST['path']));

	}

	function validate() {
		// insert proper validation HERE
		return true;
	}

	function save( $params=array() ) {

		// define/filter the data set
		$fields = array("id", "title", "content", "tags", "template");
		$data = array_fill_keys($fields, "");
		$data = array_merge($data, $params);

		if( array_key_exists("id", $params) ){
			// Update existing page
			$page=new Page($data['id']);
			$page->set('title', 	$data['title']);
			$page->set('content', 	$data['content']);
			$page->set('tags', 		$data['tags']);
			$page->set('template', 	$data['template']);
			$page->update();
		} else {
			// Create new page
			$page=new Page();
			$page->set('title', 	$data['title']);
			$page->set('content', 	$data['content']);
			$page->set('tags', 		$data['tags']);
			$page->set('template', 	$data['template']);
			$page->set('path', 		$data['path']);
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
		header('Location: '.url());
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