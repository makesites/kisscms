<?php
class Config extends Model {

  function __construct($table='sqlite_master') {
	$this->tablename = $table;
	parent::__construct('config.sqlite', 'key', $this->tablename); //primary key = id; tablename = pages
	$this->rs['key'] = '';
    $this->rs['value'] = '';
	if($table=='sqlite_master')
		// the the whole config table and save it as a global variable
		$GLOBALS['config'] = $this->get_tables();
  }

  static function register($table, $key, $value="") {
	// stop if variable already available
	if(array_key_exists($table, $GLOBALS['config']) && array_key_exists($key, $GLOBALS['config'][$table])) return false;
	
	$config = new Config($table);
	
	// then check if the table exists
	if(!array_key_exists($table, $GLOBALS['config'])){
		$config->create_table($table);
	}
	
	// just create the key
	if( !array_key_exists($key, $GLOBALS['config'][$table])) {
		$config->set('key', "$key");
		$config->set('value', "$value");
		$config->create();
		// save in the global object
		$GLOBALS['config'][$table][$key] = $value;
	}
  }
  
  function create_table($name){
	$dbh= $this->getdbh();
	$sql = "CREATE TABLE $name(key,value)";
    $results = $dbh->prepare($sql);
	print_r( $sql );
    //$results->bindValue(1,$username);
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
			$myclass = new $class($this->tablename);
			foreach ($rs as $key => $val)
				if (isset($myclass->rs[$key]))
					$myclass->rs[$key] = is_scalar($myclass->rs[$key]) ? $val : unserialize($this->COMPRESS_ARRAY ? gzinflate($val) : $val);
				// this part is awefully hardcoded to achieve the desirable array strucutre...
				$arr[ $myclass->rs['key'] ]= $myclass->rs['value'];
		}
		return $arr;
	}
	
}
?>