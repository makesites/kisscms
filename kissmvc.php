<?php
/*****************************************************************
Copyright (c) 2008 {kissmvc.php version 0.2}
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
function requestRouter() {
  $controller=defined('DEFAULT_ROUTE')?DEFAULT_ROUTE:'main';
  $action=defined('DEFAULT_ACTION')?DEFAULT_ACTION:'index';
  $params=array();
  if (function_exists('requestParserCustom'))
    requestParserCustom($controller,$action,$params);
  else
    requestParser($controller,$action,$params);
  if (function_exists('controllerRouterCustom'))
    require(controllerRouterCustom($controller));
  else
    require(controllerRouter($controller));
  if (!function_exists($action))
    die(viewFetch('404.php'));
  call_user_func_array($action,$params);
}

//This function parses the HTTP request to get the controller, action and parameter parts.
function requestParser(&$controller,&$action,&$params) {
  $requri=preg_replace('#^'.addslashes(WEB_FOLDER).'#', '', $_SERVER['REQUEST_URI']);
  preg_match('#^([^/]+)\/{0,1}$#', $requri, $matches);
  if (count($matches)==2)
    $controller=$matches[1];
  else {
    preg_match('#^([^/]+)/([^/]+)/?(.*)$#', $requri, $matches);
    if (isset($matches[1]))
      $controller=$matches[1];
    if (isset($matches[2]))
      $action=$matches[2];
    if (isset($matches[3]) && $matches[3])
      $params=explode('/',$matches[3]);
  }
  if (!preg_match('#^[A-Za-z0-9_-]+$#',$action) || function_exists($action))
    die(viewFetch('404.php'));
}

//This function maps the controller name to the file location of the .php file to include
function controllerRouter($controller) {
  $controllerfile=APP_PATH.'controllers/'.$controller.'.php';
  if (!preg_match('#^[A-Za-z0-9_-]+$#',$controller) || !file_exists($controllerfile))
    die(viewFetch('404.php'));
  return $controllerfile;
}

//===============================================================
// View
// Various ways to include and return plain PHP templates
//===============================================================
function viewFetch($filename,&$vars=null) {
  if (is_array($vars))
    extract($vars);
  ob_start();
  require(APP_PATH.'views/'.$filename);
  return ob_get_clean();
}

function viewDump($filename,&$vars=null) {
  if (is_array($vars))
    extract($vars);
  require(APP_PATH.'views/'.$filename);
}

function viewFetchStr($str,&$vars=null) {
  if (is_array($vars))
    extract($vars);
  ob_start();
  eval('?>'.$str);
  return ob_get_clean();
}

function viewDumpStr($str,&$vars=null) {
  if (is_array($vars))
    extract($vars);
  eval('?>'.$str);
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
      $GLOBALS['dbh']->exec("SET sql_mode='ANSI_QUOTES'"); //this line is required for pdo-mysql
    } catch (PDOException $e) {
      die('Connection failed: '.$e->getMessage());
    }
  return $GLOBALS['dbh'];
}
*/
//===============================================================
class crude {

  var $conf = array();
  var $rs = array();

  function crude($pkname='',$tablename='') {
    $this->conf['pkname']=$pkname; //Name of auto-incremented Primary Key
    $this->conf['tablename']=$tablename; //Corresponding table in database
  }

  function pkname() {
    return $this->conf['pkname'];
  }

  function pkvalue() {
    return $this->rs[$this->conf['pkname']];
  }

  function tablename() {
    return $this->conf['tablename'];
  }

  function set($key, $val) {
    if (isset($this->rs[$key]))
      $this->rs[$key] = $val;
  }

  function get($key) {
    return $this->rs[$key];
  }

  //Inserts record into database with a new auto-incremented primary key
  //Assumes primary key is set to auto increment
  function create() {
    $dbh=getdbh();
    $pkname=$this->pkname();
    $this->set($pkname,'');
    $s1=$s2='';
    foreach ($this->rs as $k => $v)
      if ($k!=$pkname && is_scalar($v)) {
        $s1 .= ',"'.$k.'"';
        $s2 .= ',?';
      }
    $sql = 'INSERT INTO "'.$this->tablename().'" ('.substr($s1,1).') VALUES ('.substr($s2,1).')';
    $stmt = $dbh->prepare($sql);
    $i=0;
    foreach ($this->rs as $k => $v)
      if ($k!=$pkname && is_scalar($v))
        $stmt->bindValue(++$i,$v);
    $stmt->execute();
    if (!$stmt->rowCount())
      return false;
    $this->set($pkname,$dbh->lastInsertId());
    return true;
  }

  function retrieve($pkvalue) {
    $dbh=getdbh();
    $sql = 'SELECT * FROM "'.$this->tablename().'" WHERE "'.$this->pkname().'"=?';
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1,$pkvalue);
    $stmt->execute();
    $rs = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$rs)
      return false;
    foreach ($rs as $k => $v)
      $this->set($k,$v);
    return true;
  }

  function update() {
    $dbh=getdbh();
    $s='';
    foreach ($this->rs as $k => $v)
      if (is_scalar($v))
        $s .= ',"'.$k.'"=?';
    $s = substr($s,1);
    $sql = 'UPDATE "'.$this->tablename().'" SET '.$s.' WHERE "'.$this->pkname().'"=?';
    $stmt = $dbh->prepare($sql);
    $i=0;
    foreach ($this->rs as $k => $v)
      if (is_scalar($v))
        $stmt->bindValue(++$i,$v);
    $stmt->bindValue(++$i,$this->pkvalue());
    return $stmt->execute();
  }

  function delete() {
    $dbh=getdbh();
    $sql = 'DELETE FROM "'.$this->tablename().'" WHERE "'.$this->pkname().'"=?';
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1,$this->pkvalue());
    return $stmt->execute();
  }

  function exists() {
    if (!$this->rs[$this->pkname()])
      return false;
    $dbh=getdbh();
    $sql = 'SELECT 1 FROM "'.$this->tablename().'" WHERE "'.$this->pkname()."\"='".$this->pkvalue()."'";
    $result = $dbh->query($sql)->fetchAll();
    return count($result);
  }
}