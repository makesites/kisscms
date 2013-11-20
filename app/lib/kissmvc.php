<?php
/*****************************************************************
Copyright (c) 2008-2009 {kissmvc.php version 0.6}
Eric Koh <erickoh75@gmail.com> http://kissmvc.com

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
*****************************************************************/
//===============================================================
// Controller
// Parses the HTTP request and routes to the appropriate function
//===============================================================
class KISS_Controller {
	public $controller_path='../app/controllers/'; //with trailing slash
	public $web_folder='/'; //with trailing slash
	public $default_controller='main';
	public $default_function='index';
	public $request_uri_parts=array();

	function __construct($controller_path,$web_folder,$default_controller,$default_function)  {
		$this->controller_path=$controller_path;
		$this->web_folder=$web_folder;
		$this->default_controller=$default_controller;
		$this->default_function=$default_function;
		$this->parse_http_request();
		$this->route_request();
	}

	//This function parses the HTTP request to set the controller name, function name and parameter parts.
	function parse_http_request() {
		$requri = $_SERVER['REQUEST_URI'];
		if (strpos($requri,$this->web_folder)===0)
			$requri=substr($requri,strlen($this->web_folder));
		$this->request_uri_parts = $requri ? explode('/',$requri) : array();
		return $this;
	}

	//This function maps the controller name and function name to the file location of the .php file to include
	function route_request() {
		$controller = $this->default_controller;
		$function = $this->default_function;
		$params = array();

		$p = $this->request_uri_parts;
		if (isset($p[0]) && $p[0])
			$controller=$p[0];
		if (isset($p[1]) && $p[1])
			$function=$p[1];
		if (isset($p[2]))
			$params=array_slice($p,2);

		$controllerfile=$this->controller_path.$controller.'/'.$function.'.php';
		if (!preg_match('#^[A-Za-z0-9_-]+$#',$controller) || !file_exists($controllerfile))
			$this->request_not_found();

		$function='_'.$function;
		if (!preg_match('#^[A-Za-z_][A-Za-z0-9_-]*$#',$function) || function_exists($function))
			$this->request_not_found();
		require($controllerfile);
		if (!function_exists($function))
			$this->request_not_found();

		call_user_func_array($function,$params);
		return $this;
	}

	//Override this function for your own custom 404 page
	function request_not_found() {
		header("HTTP/1.0 404 Not Found");
		die('<html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL was not found on this server.</p><p>Please go <a href="javascript: history.back(1)">back</a> and try again.</p><hr /><p>Powered By: <a href="http://kissmvc.com">KISSMVC</a></p></body></html>');
	}
}

//===============================================================
// View
// For plain .php templates
//===============================================================
class KISS_View {
  public $file='';
  public $vars=array();

  function __construct($file='',$vars='')  {
    if ($file)
      $this->file = $file;
    if (is_array($vars))
      $this->vars=$vars;
    return $this;
  }

  function __set($key,$var) {
    return $this->set($key,$var);
  }

	function set($key,$var) {
		$this->vars[$key]=$var;
		return $this;
	}

	//for adding to an array
	function add($key,$var) {
		$this->vars[$key][]=$var;
	}

  function fetch($vars='') {
    if (is_array($vars))
      $this->vars=array_merge($this->vars,$vars);
    extract($this->vars);
    ob_start();
    require($this->file);
    return ob_get_clean();
  }

  function dump($vars='') {
    if (is_array($vars))
      $this->vars=array_merge($this->vars,$vars);
    extract($this->vars);
    require($this->file);
  }

	static function do_fetch($file='',$vars='') {
		if (is_array($vars))
			extract($vars);
		ob_start();
		require($file);
		return ob_get_clean();
	}

	static function do_dump($file='',$vars='') {
		if (is_array($vars))
			extract($vars);
		require($file);
	}

	static function do_fetch_str($str,$vars='') {
		if (is_array($vars))
			extract($vars);
		ob_start();
		eval('?>'.$str);
		return ob_get_clean();
	}

	static function do_dump_str($str,$vars='') {
		if (is_array($vars))
			extract($vars);
		eval('?>'.$str);
	}
}

//===============================================================
// Model/ORM
// Requires a function getdbh() which will return a PDO handler
/*
function getdbh() {
	if (!isset($GLOBALS['dbh']))
		try {
			//$GLOBALS['dbh'] = new PDO('sqlite:'.APP_PATH.'db/dbname.sqlite');
			$GLOBALS['dbh'] = new PDO('mysql:host=localhost;dbname=dbname', 'username', 'password');
		} catch (PDOException $e) {
			die('Connection failed: '.$e->getMessage());
		}
	return $GLOBALS['dbh'];
}
*/
//===============================================================
class KISS_Model  {

	public $pkname;
	public $tablename;
	public $dbhfnname;
	public $QUOTE_STYLE='MYSQL'; // valid types are MYSQL,MSSQL,ANSI
	public $COMPRESS_ARRAY=true;
	public $rs = array(); // for holding all object property variables

	function __construct($pkname='',$tablename='',$dbhfnname='getdbh',$quote_style='MYSQL',$compress_array=true) {
		$this->pkname=$pkname; //Name of auto-incremented Primary Key
		$this->tablename=$tablename; //Corresponding table in database
		$this->dbhfnname=$dbhfnname; //dbh function name
		$this->QUOTE_STYLE=$quote_style;
		$this->COMPRESS_ARRAY=$compress_array;
	}

	function get($key) {
		return $this->rs[$key];
	}

	function set($key, $val) {
		if (isset($this->rs[$key]))
			$this->rs[$key] = $val;
		return $this;
	}

	function __get($key) {
		return $this->get($key);
	}

	function __set($key, $val) {
		return $this->set($key,$val);
	}

	protected function getdbh() {
		return call_user_func($this->dbhfnname);
	}

	protected function enquote($name) {
		if ($this->QUOTE_STYLE=='MYSQL')
			return '`'.$name.'`';
		elseif ($this->QUOTE_STYLE=='MSSQL')
			return '['.$name.']';
		else
			return '"'.$name.'"';
	}

	//Inserts record into database with a new auto-incremented primary key
	//If the primary key is empty, then the PK column should have been set to auto increment
	function create() {
		$dbh=$this->getdbh();
		$pkname=$this->pkname;
		$s1=$s2='';
		foreach ($this->rs as $k => $v)
			if ($k!=$pkname || $v) {
				$s1 .= ','.$this->enquote($k);
				$s2 .= ',?';
			}
		$sql = 'INSERT INTO '.$this->enquote($this->tablename).' ('.substr($s1,1).') VALUES ('.substr($s2,1).')';
		$stmt = $dbh->prepare($sql);
		$i=0;
		foreach ($this->rs as $k => $v)
			if ($k!=$pkname || $v)
				$stmt->bindValue(++$i,is_scalar($v) ? $v : ($this->COMPRESS_ARRAY ? gzdeflate(serialize($v)) : serialize($v)) );
		$stmt->execute();
		if (!$stmt->rowCount())
			return false;
		$this->set($pkname,$dbh->lastInsertId());
		return $this;
	}

	function retrieve($pkvalue) {
		$dbh=$this->getdbh();
		$sql = 'SELECT * FROM '.$this->enquote($this->tablename).' WHERE '.$this->enquote($this->pkname).'=?';
		$stmt = $dbh->prepare($sql);
		$stmt->bindValue(1,(int)$pkvalue);
		$stmt->execute();
		$rs = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($rs)
			foreach ($rs as $key => $val)
				if (isset($this->rs[$key]))
					$this->rs[$key] = is_scalar($this->rs[$key]) ? $val : unserialize($this->COMPRESS_ARRAY ? gzinflate($val) : $val);
		return $this;
	}

	function update() {
		$dbh=$this->getdbh();
		$s='';
		foreach ($this->rs as $k => $v)
			$s .= ','.$this->enquote($k).'=?';
		$s = substr($s,1);
		$sql = 'UPDATE '.$this->enquote($this->tablename).' SET '.$s.' WHERE '.$this->enquote($this->pkname).'=?';
		$stmt = $dbh->prepare($sql);
		// #120 - if preparation failed exist now
		if( !$stmt ) return;
		$i=0;
		foreach ($this->rs as $k => $v)
			$stmt->bindValue(++$i,is_scalar($v) ? $v : ($this->COMPRESS_ARRAY ? gzdeflate(serialize($v)) : serialize($v)) );
		$stmt->bindValue(++$i,$this->rs[$this->pkname]);
		return $stmt->execute();
	}

	function delete() {
		$dbh=$this->getdbh();
		$sql = 'DELETE FROM '.$this->enquote($this->tablename).' WHERE '.$this->enquote($this->pkname).'=?';
		$stmt = $dbh->prepare($sql);
		$stmt->bindValue(1,$this->rs[$this->pkname]);
		return $stmt->execute();
	}

	//returns true if primary key is a positive integer
	//if checkdb is set to true, this function will return true if there exists such a record in the database
	function exists($checkdb=false) {
		if ((int)$this->rs[$this->pkname] < 1)
			return false;
		if (!$checkdb)
			return true;
		$dbh=$this->getdbh();
		$sql = 'SELECT 1 FROM '.$this->enquote($this->tablename).' WHERE '.$this->enquote($this->pkname)."='".$this->rs[$this->pkname]."'";
		$result = $dbh->query($sql)->fetchAll();
		return count($result);
	}

	function merge($arr) {
		if (!is_array($arr))
			return false;
		foreach ($arr as $key => $val)
			if (isset($this->rs[$key]))
				$this->rs[$key] = $val;
		return $this;
	}

	function retrieve_one($wherewhat,$bindings) {
		$dbh=$this->getdbh();
		if (is_scalar($bindings))
			$bindings=$bindings ? array($bindings) : array();
		$sql = 'SELECT * FROM '.$this->enquote($this->tablename);
		if (isset($wherewhat) && isset($bindings))
			$sql .= ' WHERE '.$wherewhat;
		$sql .= ' LIMIT 1';
		$stmt = $dbh->prepare($sql);
		$stmt->execute($bindings);
		$rs = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$rs)
			return false;
		foreach ($rs as $key => $val)
			if (isset($this->rs[$key]))
				$this->rs[$key] = is_scalar($this->rs[$key]) ? $val : unserialize($this->COMPRESS_ARRAY ? gzinflate($val) : $val);
		return $this;
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
			$myclass = new $class();
			foreach ($rs as $key => $val)
				if (isset($myclass->rs[$key]))
					$myclass->rs[$key] = is_scalar($myclass->rs[$key]) ? $val : unserialize($this->COMPRESS_ARRAY ? gzinflate($val) : $val);
			$arr[]=$myclass;
		}
		return $arr;
	}

	function select($selectwhat='*',$wherewhat='',$bindings='',$pdo_fetch_mode=PDO::FETCH_ASSOC) {
		$dbh=$this->getdbh();
		if (is_scalar($bindings))
			$bindings=$bindings ? array($bindings) : array();
		$sql = 'SELECT '.$selectwhat.' FROM '.$this->tablename;
		if ($wherewhat)
			$sql .= ' WHERE '.$wherewhat;
		$stmt = $dbh->prepare($sql);
		$stmt->execute($bindings);
		return $stmt->fetchAll($pdo_fetch_mode);
	}
}