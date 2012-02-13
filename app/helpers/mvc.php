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
				// Continue logic on a specific error code (14: unable to open database file)
				$error = (string)$e->getCode();
				if( $error == "14" ){ 
					// see if there is a data directory
					if( !is_dir( DATA ) ){ 
						// create the directory with write access
						mkdir( DATA, 0775);
						// refresh page to continue past the error
						header("Location: /"); 
						exit;
					}
				} else {
			  		die('Connection failed: '.$e->getMessage());
				}
			}
		}
		return $GLOBALS[ $db_name ];
		//return call_user_func($this->dbhfnname, $this->db);
	}

	//Example of adding your own method to the core class
	function gethtmlsafe($key) {
		return htmlspecialchars($this->get($key));
	}

	
	function create_table($name, $fields, $db=false){
		$dbh = $this->getdbh();
		$sql = "CREATE TABLE $name($fields)";
		$results = $dbh->prepare($sql);
		//$results->bindValue(1,$username);
		if( $results != false ) 
			$results->execute();	
	}
	
	function get_tables(){
		//$tables = $this->retrieve_many('type="table"');
		//foreach( $tables as $table ){ 
		//$this->tablename = $table['name'];
		//}
		$dbh= $this->getdbh();
		$sql = 'SELECT name FROM sqlite_master WHERE type="table"';
		$results = $dbh->prepare($sql);
		//$results->bindValue(1,$username);
		$results->execute();
		while ($tables = $results->fetch(PDO::FETCH_ASSOC)) {
				if (!$tables)
					return false;
				foreach ($tables as $table)
					if($table != 'sqlite_sequence'){
						$this->tablename = $table;
						$vars[$this->tablename] = $this->retrieve_many();
					}
			}
		return $vars;
	}


	function retrieve_many($wherewhat='',$bindings='') {
		$dbh=$this->getdbh();
		if (is_scalar($bindings))
			$bindings=$bindings ? array($bindings) : array();
		$sql = 'SELECT * FROM '.$this->tablename;
		if ($wherewhat)
			$sql .= ' WHERE '.$wherewhat;
		$stmt = $dbh->prepare($sql);
		$stmt->execute($bindings);
		$arr=array();
		$class=get_class($this);
		while ($rs = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$myclass = new $class($this->id, $this->tablename);
			foreach ($rs as $key => $val)
				if (isset($myclass->rs[$key]))
					$myclass->rs[$key] = is_scalar($myclass->rs[$key]) ? $val : unserialize($this->COMPRESS_ARRAY ? gzinflate($val) : $val);
				$arr[]= $myclass->rs;
		}
		return $arr;
	}
	
	
	function get($key) {
		if (isset($this->rs[$key]))
			return $this->rs[$key];
		else
			return false;
	}
	
	function drop_table( $table ) {
		if( $table ){ 
			$dbh = $this->getdbh();
			$sql = "DROP TABLE $table";
			$stmt = $dbh->prepare($sql);
			//$stmt->bindValue(1,$this->rs[$this->pkname]);
			return $stmt->execute();
		} else {
			return false;
		}
	}
	
}

//===============================================================
// Controller
//===============================================================
class Controller extends KISS_Controller {
	
	public $data;
	
	function __construct($controller_path,$web_folder,$default_controller,$default_function)  {
		// add the config in the data object
		$this->data['config'] = $GLOBALS['config'];
		// set the template the controller is using
		$template = strtolower( get_class($this) ) .".php";
		$this->data['template']= ( is_file( TEMPLATES.$template ) ) ? $template : DEFAULT_TEMPLATE ;
		
		parent::__construct($controller_path,$web_folder,$default_controller,$default_function);
	}
	
	//This function parses the HTTP request to set the controller name, function name and parameter parts.
	function parse_http_request() {		
		// remove the first slash from the URI so the controller is always the first item in the array (later)
		$url = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		$url_parts = parse_url($url);
		$requri = $url_parts['path'];
		if (strpos($requri,$this->web_folder)===0)
			$requri=substr($requri,strlen($this->web_folder));
		$request_uri_parts = $requri ? explode('/',$requri) : array();
		// remove the "index.php" from the request
		if( array_key_exists(0, $request_uri_parts) && $request_uri_parts[0] == "index.php" ){ array_shift( $request_uri_parts ); }
		// add the query if available
		if( !empty($url_parts['query']) ){ 
			$queries = explode("&", $url_parts['query']);
			foreach( $queries as $query){ 
				$request_uri_parts = array_merge( $request_uri_parts, explode("=", $query) );
			}
		}
		// adding POST params
		foreach( $_POST as $key => $value ){
			$request_uri_parts[] = $key;
			$request_uri_parts[] = $value;
		}
		$this->request_uri_parts = $request_uri_parts;
		return $this;
	}

	//This function maps the controller name and function name to the file location of the .php file to include
	function route_request( $route=false) {
		$controller = $this->default_controller;
		$function = $this->default_function;
		$class = strtolower( get_class($this) );
		$params = array();

		$p = ( !$route ) ? $this->request_uri_parts : $route;
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
			if (isset($p[0]) && method_exists($this, $p[0])) {
				$function=$p[0];
				$params = array_slice($p,1);
			} else {
				$params = $p;
			}
		}
		
		// lastly convert the params in pairs
		$params = $this->beautify_array( $params );
		
		// if the method doesn't exist rever to a generic 404 page
		if (!preg_match('#^[A-Za-z_][A-Za-z0-9_-]*$#',$function) || !method_exists($this, $function))
			$this->request_not_found();
		
		
		// calculate the path - possibly this can be merged with parse_http_request()
		$path = preg_replace('#^'.addslashes(WEB_FOLDER).'#', '', $_SERVER['REQUEST_URI']);
		// check if we have a trailing slash (and remove it) 
		$path = ( substr($path, -1) == "/" ) ? substr($path, 0, -1) : $path;
		// save the path for later use by controllers and helpers
		$GLOBALS['path'] = $this->data['path'] = $path; 
		
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

	function redirect($path, $window=false) {
		if($window != "top"){ 
			header('Location: '.url($path));
		} else {
			echo "<script type='text/javascript'>top.location.href = '". $path ."';</script>";
		}
		
		exit;
	}

	function render() {
		// display the page
		Template::output($this->data);
	}
	
	// this function takes an array and creates pairs of key-value
	function beautify_array( $params ){
		
		// return null if there are no params
		if( count($params)==0 ) {
			$params = null;
		// convert the params to a string if they are only one element
		} else if( count($params)==1 ) {
			$params = implode($params);
		// create a new key/value array
		} else {
			
			$newparams = array();
			
			foreach( $params as $num => $param ){
				if( $num%2 == 0 ){
					$key = $param;
					$value = $params[$num+1];
					// save the new key/value pair
					$newparams[ $key ] = $value;
				}
			}
			
			// replace the given params
			$params = $newparams;
		}
		
		return $params;
		
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