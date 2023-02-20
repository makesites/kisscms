<?php

//===============================================================
// Model/ORM
//===============================================================

if (!class_exists('Model')){

class Model extends KISS_Model  {

	public $db;

	function __construct($db='pages.sqlite', $pkname='',$tablename='',$dbhfnname='getdbh',$quote_style='MYSQL',$compress_array=true) {
		$this->db=$db; //Name of the database
		if( !isset($this->pkname) ) $this->pkname=$pkname; //Name of auto-incremented Primary Key
		if( !isset($this->tablename) ) $this->tablename=$tablename; //Corresponding table in database
		$this->dbhfnname=$dbhfnname; //dbh function name
		$this->QUOTE_STYLE=$quote_style;
		$this->COMPRESS_ARRAY=$compress_array;
		// post-construct init...
		$this->init();
	}

//===============================================
// Initialization
//===============================================

	protected function init() {
		// conditions for local dbs
		if( is_scalar($this->db) && substr( $this->db, -6 ) === "sqlite" ){
			// check if the pages table exists
			$dbh = $this->getdbh();
			$sql = "SELECT name FROM sqlite_master WHERE type='table'";
			$results = $dbh->prepare($sql);
			$results->execute();
			$table = $results->fetch(PDO::FETCH_ASSOC);
			// then check if the table exists
			if(!is_array($table)){
				$data = $this->schema();
				// exit now if there's not schema
				if( !$data ) return;
				$keys = implode(", ", array_keys( $data ));
				// FIX: The id needs to be setup as autoincrement
				$keys = str_replace("id,", "id INTEGER PRIMARY KEY ASC,", $keys);
				$this->create_table( $this->tablename, $keys );
			}
		}
	}

//===============================================
// Database Connection
//===============================================
	protected function getdbh() {
		// generate the name prefix
		$db_name = "db_" . substr( $this->db, 0, stripos($this->db, ".") );
		if (!isset($GLOBALS[ $db_name ])) {

			try {
				$db = new PDO('sqlite:'. DATA . $this->db, null, null, array(
					PDO::ATTR_PERSISTENT => true
				));
				// FIX: disable sync to improve performance
				$db->exec("pragma synchronous = off;");
				$GLOBALS[ $db_name ] = $db;
				//$GLOBALS['dbh'] = new PDO('mysql:host=localhost;dbname=dbname', 'username', 'password');
			} catch (PDOException $e) {
				// Continue logic on a specific error code (14: unable to open database file)
				$error = (string)$e->getCode();
				if( $error == "14" ){
					// #79 report last error on SQLite fail
					print_r(error_get_last());
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

	function schema( $schema=array() ){
		//
		if( !isset($this->rs) ) $this->rs = array();
		$this->rs = array_merge( $schema, $this->rs );
		$name = ( isset($this->name) ) ? $this->name : strtolower( __CLASS__  );
		// save schema in the global namespace
		if( !isset( $GLOBALS['db_schema'] ) ) $GLOBALS['db_schema'] = array();
		if( !isset( $GLOBALS['db_schema'][$name] ) ) $GLOBALS['db_schema'][$name] = array();

		$GLOBALS['db_schema'][$name] = $this->rs;

		return $this->rs;
	}

	function set($key, $val) {
		// checking if key exists instead of value...
		if ( array_key_exists($key, $this->rs) )
			$this->rs[$key] = $val;
		return $this;
	}

//===============================================
// CRUD methods
//===============================================

	function read($pkvalue) {
		$dbh=$this->getdbh();
		$sql = 'SELECT * FROM '.$this->enquote($this->tablename).' WHERE '.$this->enquote($this->pkname).'=?';
		$stmt = $dbh->prepare($sql);
		// error control
		if( !$stmt ) return $this;
		$stmt->bindValue(1,(int)$pkvalue);
		$stmt->execute();
		$rs = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($rs){
			foreach ($rs as $key => $val){
				//if (isset($this->rs[$key]))
				//$this->rs[$key] = is_scalar($this->rs[$key]) ? $val : unserialize($this->COMPRESS_ARRAY ? gzinflate($val) : $val);
				$this->rs[$key] = $val;
			}
		}
		return $this;
	}


//===============================================
// Query methods
//===============================================

	// run a lookup query based on a field
	function find($a= false, $b=false){
		$query = "";
		if( !$b && is_array( $a ) ){
			foreach( $a as $key => $value ){
				if( !empty($query) ) $query .=" AND ";
				$query .= $key ."='". $value ."'";
			}
		} else if( !$b && is_scalar( $a ) ) {
			//
			$query .= $a;
		} else if( !$b && !$a ) {
			// prerequisite
			return null;
		} else {
			// assume both are scalar...
			$query .= $a ."='". $b ."'";
		}
		// execute
		$data = $this->retrieve_many( $query );
		return $data;
	}

	// run a lookup query based on a field, returns first item
	function findOne($a= false, $b=false){
		// same as find...
		$query = "";
		if( !$b && is_array( $a ) ){
			foreach( $a as $key => $value ){
				if( !empty($query) ) $query .=" AND ";
				$query .= $key ."='". $value ."'";
			}
		} else if( !$b && is_scalar( $a ) ) {
			//
			$query .= $a;
		} else if( !$b && !$a ) {
			// prerequisite
			return null;
		} else {
			// assume both are scalar...
			$query .= $a ."='". $b ."'";
		}
		// execute
		$data = $this->retrieve_many( $query ); // replace with retrieve_one?
		// return only the first - fix this by limiting the query
		return ( count($data) ) ? $data[0] : false;
	}


//===============================================
// Table methods
//===============================================

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
		$vars = array();
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

	function merge($arr) {
		if (!is_array($arr))
			return false;
		foreach ($arr as $key => $val)
			$this->set($key, $val);
		return $this;
	}

	// merge the existing data of a key
	function extend($key=false, $data=array()){
		// prerequisite
		if(!$key) return false;
		// first get existing data
		$value = $this->get( $key );
		// get returns false only when it doesn't find a value?
		//if( $value === false ) return;
		// different condition for scalar?
		if( is_null($value) || is_scalar($value) ) {
			$value = $data;
		} else {
			// array?
			$value = array_merge( (array)$value, (array)$data );
		}
		// either way save back...
		$this->set( $key, $value);
	}

	function retrieve_many($wherewhat='',$bindings='') {
		$dbh=$this->getdbh();
		$arr=array();
		if( is_scalar($bindings) ){
			$bindings=$bindings ? array($bindings) : array();
		}
		$sql = 'SELECT * FROM '.$this->tablename;
		if( $wherewhat ){
			$sql .= ' WHERE '.$wherewhat;
		}
		$stmt = $dbh->prepare($sql);
		if( !$stmt ) return $arr;
		$stmt->execute($bindings);
		$class=get_class($this);
		while($rs = $stmt->fetch(PDO::FETCH_ASSOC)){
			$myclass = new $class($this->id, $this->tablename);
			$myschema = $myclass->schema();
			foreach($rs as $key => $val){
				if( isset($myclass->rs[$key]) || is_null($myclass->rs[$key]) ){
					//$myclass->rs[$key] = is_scalar($myclass->rs[$key]) ? $val : unserialize($this->COMPRESS_ARRAY ? gzinflate($val) : $val);
					$myclass->rs[$key] = is_scalar( $myschema[$key] ) ? $val : json_decode($val, true);
				}
			}
			$arr[]= $myclass->rs;
		}
		return $arr;
	}

	function get($key) {
		if (isset($this->rs[$key]))
			return $this->rs[$key];
		else
			return null;
	}

	function getAll(){
		// override in models to add exceptions...
		$array = array();
		foreach($this->rs as $k=>$v){
			$result = $this->get($k);
			// don't add data that 's returned as 'false'
			if( !is_null($result) ) $array[$k] = ( is_string($result) ) ? stripslashes( $result ) : $result;
		}
		return $array;
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

//===============================================
// Helper methods
//===============================================

	//Example of adding your own method to the core class
	function gethtmlsafe($key) {
		return htmlspecialchars($this->get($key));
	}

	// #115 generate a random UUID v4
	function uuid() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

			// 16 bits for "time_mid"
			mt_rand( 0, 0xffff ),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand( 0, 0x0fff ) | 0x4000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand( 0, 0x3fff ) | 0x8000,

			// 48 bits for "node"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}

	function CommonID() {
		//
		$now = strtotime("now");
		$random = dechex( $now * rand(1000000, 9999999) );
		return  $random . dechex( $now );
	}

}}

if (!class_exists('Controller')){
//===============================================================
// Controller
//===============================================================
class Controller extends KISS_Controller {

	public $data;
	public $_endpoint;
	
	function __construct( $controller_path='controllers/', $web_folder=WEB_FOLDER, $default_controller=DEFAULT_ROUTE, $default_function=DEFAULT_ACTION )  {
		// generic redirection for secure connections (assuming that ssl is on port 443)
		if( defined('SSL') && SSL && $_SERVER['SERVER_PORT'] != "443" ) header('Location: '. url( request_uri() ) );

		// html cache on only in production
		$cached = ( DEBUG ) ? false : $this->_pageCache();
		if( !empty($cached) ){
			echo $cached;
			// exit now
			exit;
			//return;
		}

		// add the config in the data object
		$this->data['config'] = $GLOBALS['config'];
		// add admin flag
		if( array_key_exists('admin', $_SESSION) ) $this->data['admin'] = $_SESSION['admin'];

		// set the template the controller is using
		$template = strtolower( get_class($this) ) .".php";
		$this->data['template']= ( is_file( TEMPLATES.$template ) ) ? $template : false;

		// #116 add site info in the client object
		$GLOBALS['client']['site']['name'] = $GLOBALS['config']['main']['site_name'];
		$url = url();
		// FIX: removing ending slash
		$GLOBALS['client']['site']['url'] = ( substr( $url, -1) == "/" ) ? substr( $url, 0, -1) : $url;

		parent::__construct($controller_path,$web_folder,$default_controller,$default_function);
	}

	// display the client vars
	function client_js() {
		// container
		if( !array_key_exists("_client", $_SESSION) || !is_array($_SESSION["_client"]) )
				$_SESSION["_client"] = array();
		//
		$path = null;
		if( !empty( $_SERVER["HTTP_REFERER"] ) ){
			$url = parse_url ( $_SERVER["HTTP_REFERER"] );
			$path = ( array_key_exists('path', $url) ) ? $url['path'] : "/";
		}
		// set the right header
		header('Content-Type: application/javascript');
		echo ( !empty( $_SESSION["_client"][$path] ) ) ? $_SESSION["_client"][$path] : "";
	}

	//This function parses the HTTP request to set the controller name, function name and parameter parts.
	function parse_http_request() {
		$request = array();
		// form the url
		$url = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		// remove the trailing slash, if any
		if( substr($url, -1) == "/" ) $url = substr($url, 0, -1);

		// parse the URL
		$url_parts = parse_url($url);
		$requri = ( array_key_exists("path", $url_parts) ) ? $url_parts['path'] : "";
		// remove the first slash from the URI so the controller is always the first item in the array (later)
		if (strpos($requri,$this->web_folder)===0)
			$requri=substr($requri,strlen($this->web_folder));
		// FIX: allow for controller names with extensions
		$requri = str_replace(".", "_", $requri);
		$request["uri_parts"] = $requri ? explode('/', $requri) : array();
		// remove the "index.php" from the request
		if( array_key_exists(0, $request["uri_parts"]) && $request["uri_parts"][0] == "index.php" ){ array_shift( $request["uri_parts"] ); }

		// add GET params
		if( !empty($url_parts['query']) ){
			$queries = explode("&", $url_parts['query']);
			$request["query"] = array();
			foreach( $queries as $query){
				$request["query"] = array_merge( $request["query"], explode("=", $query) );
			}
		}

		// add POST params
		if( !empty($_POST) ){
			$request["post"] = array();
			foreach( $_POST as $k => $v ){
				$request["post"][] = $k;
				$request["post"][] = $v;
			}
		}

		// handle requests encoded as application/json
		if (array_key_exists("CONTENT_TYPE",$_SERVER) && stripos($_SERVER["CONTENT_TYPE"], "application/json")===0) {
			$json = json_decode(file_get_contents("php://input"));
			$request["json"] = array();
			foreach( $json as $k => $v ){
				$request["json"][] = $k;
				$request["json"][] = $v;
			}
		}


		$this->request_uri_parts =  $request;

		return $this;
	}

	//This function maps the controller name and function name to the file location of the .php file to include
	function route_request( $route=false) {
		$controller = $this->default_controller;
		$function = $this->default_function;
		$class = strtolower( get_class($this) );
		$request = $this->request_uri_parts;
		$remove = array();

		$p = ( !$route ) ? array_collapse($request) : $route;

		if (isset($p[0]) && $p[0] == $class) {
			$controller=$p[0];
			$remove[] = $p[0];
			if (isset($p[1]) && method_exists($this, $p[1])) {
				$function=$p[1];
				$remove[] = $p[1];
			}
		} else {
			if (isset($p[0]) && method_exists($this, $p[0])) {
				$function=$p[0];
				$remove[] = $p[0];
			}
		}
		// exclude query vars
		if( !empty($request['query'])
				&& array_key_exists('site', $GLOBALS["config"])
				&& !empty($GLOBALS["config"]['site']['exclude_params']) ){
			$exclude_params = $GLOBALS["config"]['site']['exclude_params'];
			// make sure exclude_params is an array
			if( is_scalar($exclude_params) ) $exclude_params = explode("|", $exclude_params);
			$q=0;
			foreach($request['query'] as $query){
				// skip every other query pair...
				if($q%2 !== 0) continue;
				$q++;
				if( !in_array($query, $exclude_params) ) continue;
				// remove both query key and value
				$remove[] = $query;
				$remove[] = $request['query'][$q];
			}
		}

		// lastly convert the params in pairs
		$params = $this->normalize_params( $request, $remove );

		// if the method doesn't exist revert to a generic 404 page
		if (!preg_match('#^[A-Za-z_][A-Za-z0-9_-]*$#',$function) || !method_exists($this, $function))
			$this->request_not_found();


		// calculate the path - possibly this can be merged with parse_http_request()
		$path = preg_replace('#^'.addslashes(WEB_FOLDER).'#', '', $_SERVER['REQUEST_URI']);
		// remove any params from the query
		if( isset($exclude_params) ){
			foreach( $exclude_params as $param ){
				$path = preg_replace( '/'.$param.'=[A-Za-z0-9_-]*'.'/', '', $path);
			}
		}
		// check if we have a trailing slash (and remove it)
		$path = ( substr($path, -1) == "/" ) ? substr($path, 0, -1) : $path;
		// check if we have a question mark with nothing following it (and remove it)
		$path = ( substr($path, -1) == "?" ) ? substr($path, 0, -1) : $path;
		// save the path for later use by controllers and helpers
		$GLOBALS['path'] = $this->data['path'] = $path;
		// save a reference to the endpoint
		$this->_endpoint = $function;

		// call the method
		$this->$function($params);

		return $this;
	}

	//Example of overriding a core class method with your own
	function request_not_found() {
		die(View::do_fetch( getPath('views/errors/404.php') ));
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

	function render( $view=false) {
		$class = strtolower( get_class($this) );
		// #122 adding page info
		if( !array_key_exists("_page", $this->data) ) $this->data["_page"] = array();
		$this->data["_page"]['controller'] = $class;
		$this->data["_page"]['view'] = $view or "";
		// process custom body view (if available)
		if( !$view ) $view = "body-". $this->_endpoint;
		// include a default view for body sections
		//$this->data["body"][$class]["view"] = ($view) ? getPath('views/'.$class.'/'. $view .'.php') : getPath('views/'.$class.'/body.php');
		// get the actual path of the view
		$view = getPath('views/'.$class.'/'. $view .'.php');
		// FIX: ultimate fallback
		if( !$view ) $view = getPath('views/main/body.php');
		//
		if( array_key_exists("body" , $this->data ) ){
			foreach( $this->data["body"] as $k => $v ){
				if( !is_array($v) ) $v = array();
				if( !array_key_exists("view", $v) ){
					$this->data["body"][$k]["view"] = ($view) ? $view : getPath('views/'.$class.'/body.php');
				}
			}
		} else {
			// there are no body data - may still be a "static" view
			$this->data["body"] = array();
			$this->data["body"][$class]["view"] = ($view) ? $view : getPath('views/'.$class.'/body.php');
		}
		Event::trigger('page:render', $view, $this->data);
		// display the page
		Template::output($this->data);
	}

	// this function takes an array and creates pairs of key-value
	function normalize_params( $params, $remove){

		// create a new key/value array
		$normalized = array();

		// first remove the picked route
		if( !empty($remove) ){
			// route is either part of the path or the query
			// remove the selected route from the request
			if( !empty($params["uri_parts"]) ){
				$params["uri_parts"] = array_remove($params["uri_parts"], $remove);
			} else {
				$params["query"] = array_remove($params["query"], $remove);
			}
		}

		//loop through the groups of params
		foreach( $params as $type => $group ){

			while ( $param = current($group) ){
				$next = next($group);
				if( $next === false ){
					 $normalized[] = $param;
				} else {
					$key = $param;
					$value = $next;
					// save the new key/value pair
					$normalized[ $key ] = $value;
					next($group);
				}

			}
		}

		// replace the given params
		$params = $normalized;

		// return false if there are no params
		if( count($params)==0 ) {
			$params = false;
		// convert the params to a string if they are only one element
		} else if( count($params)==1 && isset($params[0]) ) {
			$params = implode($params);
		}

		return $params;

	}

	/**
	 *  CORS-compliant method.  It will allow any GET, POST, PUT or DELETE requests from any origin.
	 *
	 *  This is a test feature. In a production environment, you probably want to be more restrictive
	 *  Will add whitelist domain as part of the configuration. For now, use with caution...
	 *
	 *  Example:
	 *  $this->cors();
	 *  $this->render();
	 *
	 */
	function cors() {

		// Allow from any origin
		if ( isset($_SERVER['HTTP_ORIGIN']) ) {
			header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
			header("Access-Control-Allow-Credentials: true");
		} else {
			header("Access-Control-Allow-Origin: *");
		}
			header("Access-Control-Allow-Headers: X-CSRF-Token, X-Requested-With, Accept, Accept-Version, Content-Length, Content-MD5, Content-Type, Date, X-Api-Version, X-PINGOTHER");
			header("Access-Control-Max-Age: 86400");    // cache for 1 day
			header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

	}

	// Internal
	function _pageCache(){
		// compile md5 from: session id + request data + request uri
		$key = md5( session_id() . json_encode($_REQUEST) . $_SERVER['REQUEST_URI'] );
		// if a file with that id exists and it's relatively new, use it...
		$id = "{$_SERVER['HTTP_HOST']}/html/". $key;
		$cache = Template::getCache( $id );
		// return cache if any...
		return $cache;
	}

}}

if (!class_exists('View')){
//===============================================================
// View
//===============================================================
class View extends KISS_View {

	//Example of overriding a constructor/method, add some code then pass control back to parent
	function __construct($file='',$vars='') {
		$file =  getPath('views/'.$file);
		return parent::__construct($file,$vars);
	}

	static function do_fetch($file='',$vars='') {
		$view = parent::do_fetch($file,$vars);
		// post-event
		Event::trigger('view:parse', $view, $vars );
		return $view;
	}

}}

?>
