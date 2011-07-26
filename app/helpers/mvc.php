<?php

//===============================================================
// Model/ORM
//===============================================================
class Model extends KISS_Model  {

	public $db;
	
	function __construct($db='pages.sqlite', $pkname='',$tablename='',$dbhfnname='getdbh',$quote_style='MYSQL',$compress_array=true) {
		$this->db=$db; //Name of the database
		$this->pkname=$pkname; //Name of auto-incremented Primary Key
		$this->tablename=$tablename; //Corresponding table in database
		$this->dbhfnname=$dbhfnname; //dbh function name
		$this->QUOTE_STYLE=$quote_style;
		$this->COMPRESS_ARRAY=$compress_array;
	}
	
//===============================================
// Database Connection
//===============================================

	protected function getdbh() {
		// generate the name prefix
		$db_name = "db_" . substr( $this->db, 0, stripos($this->db, ".") );
		if (!isset($GLOBALS[ $db_name ])) {
			try {
			  $GLOBALS[ $db_name ] = new PDO('sqlite:'. DATA . $this->db);
			  //$GLOBALS['dbh'] = new PDO('mysql:host=localhost;dbname=dbname', 'username', 'password');
			} catch (PDOException $e) {
			  die('Connection failed: '.$e->getMessage());
			}
		}
		return $GLOBALS[ $db_name ];
		//return call_user_func($this->dbhfnname, $this->db);
	}

	//Example of adding your own method to the core class
	function gethtmlsafe($key) {
		return htmlspecialchars($this->get($key));
	}

}

//===============================================================
// Controller
//===============================================================
class Controller extends KISS_Controller {

	//This function parses the HTTP request to set the controller name, function name and parameter parts.
	function parse_http_request() {
		// remove the first slash from the URI so the controller is always the first item in the array (later)
		$requri = $_SERVER['REQUEST_URI'];
		if (strpos($requri,$this->web_folder)===0)
			$requri=substr($requri,strlen($this->web_folder));
		$request_uri_parts = $requri ? explode('/',$requri) : array();
		// remove the "index.php" from the request
		if( array_key_exists(0, $request_uri_parts) && $request_uri_parts[0] == "index.php" ){ array_shift( $request_uri_parts ); }
		$this->request_uri_parts = $request_uri_parts;
		return $this;
	}

	//This function maps the controller name and function name to the file location of the .php file to include
	function route_request() {
		$controller = $this->default_controller;
		$function = $this->default_function;
		$class = strtolower( get_class($this) );
		$params = array();

		$p = $this->request_uri_parts;
		//findController($url)
		if (isset($p[0]) && $p[0] == $class) {
			$controller=$p[0];
			if (isset($p[1]) && method_exists($this, $p[1])) {
				$function=$p[1];
				$params = array_slice($p,2);
			} else {
				$params = array_slice($p,1);
			}
		} else {
			$params = $p;
		}
		
		// finally convert the params to a string if they are only one element
		if( count($params)==0 ) $params = null;
		if( count($params)==1 ) $params = implode($params);
		
		// if the method doesn't exist rever to a generic 404 page
		if (!preg_match('#^[A-Za-z_][A-Za-z0-9_-]*$#',$function) || !method_exists($this, $function))
			$this->request_not_found();
		
		// call the method
		$this->$function($params);
		return $this;
	}

	//Example of overriding a core class method with your own
	function request_not_found() {
		die(View::do_fetch(  getPath('views/errors/404.php') ));
	}
	
	function require_login() {
	  if (!isset($_SESSION['admin']) && $_SERVER['REQUEST_URI'] != WEB_FOLDER.'admin/login')
		$this->redirect('admin/login');
	}

	function redirect($url,$alertmsg='') {
		header('Location: '.myUrl($url));
		exit;
	}


}

//===============================================================
// View
//===============================================================
class View extends KISS_View {

	//Example of overriding a constructor/method, add some code then pass control back to parent
	function __construct($file='',$vars='') {
		$file =  getPath('views/'.$file);
		return parent::__construct($file,$vars);
	}

}

?>