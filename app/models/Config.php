<?php
class Config extends Model {

	function __construct($id=0, $table='sqlite_master') {
	$this->id = $id;
	$this->pkname = 'key';
	$this->tablename = $table;
	parent::__construct('config.sqlite', $this->pkname, $this->tablename); //primary key = id; tablename = sqlite_master
	$this->rs['key'] = '';
		$this->rs['value'] = '';
	}

	static function register($table, $key, $value="") {
	// stop if variable already available
	if( !empty($GLOBALS['config'][$table][$key]) ) return false;

	// then check if the table exists
	if( empty($GLOBALS['config'][$table]) ){
		$config = new Config(0, $table);
		$config->create_table($table, implode(",", array_keys( $config->rs )) );
		$GLOBALS['config'][$table] = array();
	}

	// just create the key
	if( empty($GLOBALS['config'][$table][$key]) ) {
		$config = new Config(0, $table);
		$config->set('key', "$key");
		$config->set('value', "$value");
		$config->create();
		// save in the global object
		$GLOBALS['config'][$table][$key] = $value;
	}

	}

	function getConfig(){
		$config = array();
		// get the raw db output
		$table_rows = $this->get_tables();
		// exit if no config is returned
		if( !is_array( $table_rows ) ){ return false; }
		// clean up data in a better format
		foreach( $table_rows as $table => $rows ){
			foreach( $rows as $row ){
				$config[$table][$row['key']] = $row['value'];
			}
		}
		// verify config against the setup
		foreach( $config as $type => $properties ){
		$is_plugin = getPath( $type ."/bin/config.php");
		$is_controller = getPath( "controllers/". $type .".php");
		 // delete the config entry if no controller/plugin found
		if( !$is_plugin && !$is_controller ){
			unset($config[$type]);
			$this->unregister($type);
		}
		}
		return $config;
	}

	function unregister($table=false){
		if( $table ){
			$result = $this->drop_table( $table );
		// possibly do something with $result here
		}
	}
}
?>