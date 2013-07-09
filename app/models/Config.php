<?php
class Config extends Model {

	function __construct($id=0, $table='sqlite_master') {
		$this->id = $id;
		$this->pkname = 'id';
		$this->tablename = $table;
		parent::__construct('config.sqlite', $this->pkname, $this->tablename); //primary key = id; tablename = sqlite_master
		$this->rs['id'] = $id;
		$this->rs['key'] = '';
		$this->rs['value'] = '';
		if ($id) $this->retrieve($id);
	}

	static function register($table, $key, $value="") {
		// create the global config object if not available
		if(!array_key_exists('config', $GLOBALS)) $GLOBALS['config'] = array();
		// exit now if variable already available
		$key_exists = ( array_key_exists($table, $GLOBALS['config']) && array_key_exists($key, $GLOBALS['config'][$table]) && !(empty($GLOBALS['config'][$table][$key]) && is_null($GLOBALS['config'][$table][$key])) );
		if ( $key_exists ) return;

		// then check if the table exists
		if( empty($GLOBALS['config'][$table]) ){
			$config = new Config(0, $table);
			// FIX: The id needs to be setup as autoincrement
			//$config->create_table($table, "id INTEGER PRIMARY KEY ASC," . implode(",", array_keys( $config->rs )) );
			$config->create_table($table, "id INTEGER PRIMARY KEY ASC, key, value");
			$GLOBALS['config'][$table] = array();
		}

		// we already know the key doesn't exist - just create it
		$config = new Config(0, $table);
		$config->set('key', "$key");
		$config->set('value', "$value");
		$config->create();
		// save in the global object
		$GLOBALS['config'][$table][$key] = $value;

	}

	// loading config - removing duplicate entries
	function getConfig(){
		$config = array();
		// get the raw db output
		$table_rows = $this->get_tables();
		// exit if no config is returned
		if( !is_array( $table_rows ) ){ return false; }
		// clean up data in a better format

		foreach( $table_rows as $table => $rows ){
			// create the config table if it doesn't exist
			if( !array_key_exists($table, $config) ) $config[$table] = array();
			foreach( $rows as $row ){
				// delete a duplicate key
				if( array_key_exists( $row['key'], $config[$table] ) ){
					// backwards compatibility - see if there's an id available
					if( $row['id'] ){
						$c = new Config($row['id'], $table);
						// delete entry
						$c->delete();
					}
				} else {
					$config[$table][$row['key']] = $row['value'];
				}
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