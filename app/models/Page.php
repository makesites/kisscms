<?php
class Page extends Model {

	function __construct($id=false, $table='pages') {
		// configuration
		$this->pkname = 'id';
		$this->tablename = $table;
		// initiate parent constructor
		parent::__construct('pages.sqlite',  $this->pkname, $this->tablename); //primary key = id; tablename = pages
		// the model
		$this->schema();
		// data container
		if( !isset($this->rs) ) $this->rs = array();
		// retrieve the specific page (if available)
		if ($id){
			$this->retrieve($id);
			$this->id = $id;
		}

	}

	function retrieve( $id ) {
		parent::read( $id );
		// post event
		Event::trigger('page:read', $this->rs );
	}

	function create( $key="", $params=array() ) {
		// clear all null keys (use array_filter?)
		foreach( $this->rs as $k=>$v){
			if( is_null($v) ) unset( $this->rs[$k] );
		}
		// timestamp
		$this->rs['created'] = time();
		$this->rs['updated'] = time();
		return parent::create();
	}

	function update() {
		// clear all null keys (use array_filter?)
		foreach( $this->rs as $k=>$v){
			if( is_null($v) ) unset( $this->rs[$k] );
		}
		$this->rs['updated'] = time();
		return parent::update();
	}

	function schema( $schema=array() ){

		$schema = array(
			'id' => '',
			'title' => '',
			'content' => '',
			'path' => '',
			'created' => '',
			'updated' => '',
			'tags' => '',
			'template' => ''
		);

		// merge with existing rs if it exists
		$schema = ( isset($this->rs) ) ? array_merge( $schema, array_filter($this->rs) ) : $schema;

		// get the existing columns
		$dbh= $this->getdbh();
		//$sql = "PRAGMA TABLE_INFO('pages')";
		$sql = "SELECT * FROM pages LIMIT 1";
		$results = $dbh->prepare($sql);
		if( $results ){
			$results->execute();
			$page = $results->fetch(PDO::FETCH_ASSOC);
			if( is_array($page) ){
				foreach( $page as $key => $value ){
					if( !array_key_exists($key, $schema) ) $schema[$key] = "";
				}
			}
		} else {
			// create table 
			$keys = implode(", ", array_keys( $schema ) );
			// FIX: The id needs to be setup as autoincrement
			$keys = str_replace("id,", "id INTEGER PRIMARY KEY ASC,", $keys);
			$page->create_table("pages", $keys );
		}

		foreach( $schema as $key => $value ){
			if( !array_key_exists($key, $this->rs) ) $this->rs[$key] = null;
			// add the column if necessary		
			$sql = "SELECT COUNT(*) AS OK FROM pragma_table_info('pages') WHERE name='$key'";
			$results = $dbh->prepare( $sql );
			$results->execute();
			$column = $results->fetch(PDO::FETCH_ASSOC);
			// 
			if( $column["OK"] == 0 ){
				$sql = "ALTER TABLE pages ADD COLUMN ". $key;
				$results = $dbh->prepare($sql);
				if( $results ) $results->execute(); 
			}
		}

		// save schema in the global namespace
		if( !isset( $GLOBALS['db_schema'] ) ) $GLOBALS['db_schema'] = array();
		//if( !isset( $GLOBALS['db_schema']['pages'] ) ) $GLOBALS['db_schema']['pages'] = array();
		// update schema
		$GLOBALS['db_schema']['pages'] = array_keys( $schema );

		return $schema;
	}

	function get_page_from_path( $uri ) {
		$dbh= $this->getdbh();
		$sql = 'SELECT * FROM "pages" WHERE "path"="'. $uri . '" LIMIT 1';
		$results = $dbh->prepare($sql);
		//$results->bindValue(1,$username);
		$results->execute();
		$page = $results->fetch(PDO::FETCH_ASSOC);
		if (!$page)
			return false;
		foreach ($page as $k => $v)
			$this->set($k,$v);
		// post event
		Event::trigger('page:read', $this->rs );
		return true;
	}


	static function register($id, $key=false, $value="") {

	// stop if variable already available
	//if(array_key_exists("pages", $GLOBALS['db_schema']) && in_array($key, $GLOBALS['db_schema']['pages'])) return;

	$page = new Page();
	$columns = $GLOBALS['db_schema']['pages'];
	$dbh= $page->getdbh();

	// check if the pages table exists
	$sql = "SELECT name FROM sqlite_master WHERE type='table' and name='pages'";
	$results = $dbh->prepare($sql);
	$results->execute();
	$table = $results->fetch(PDO::FETCH_ASSOC);

	// [DEPRECATED] then check if the table exists
	if(!is_array($table)){
		$keys = implode(", ", $columns);
		// FIX: The id needs to be setup as autoincrement
		$keys = str_replace("id,", "id INTEGER PRIMARY KEY ASC,", $keys);
		$page->create_table("pages", $keys );
		//$page->create_table("pages", "id INTEGER PRIMARY KEY ASC, title, content, path, date, tags, template");
	}
	
	// [DEPRECATED] add the column if necessary
	$sql = "SELECT COUNT(*) AS OK FROM pragma_table_info('pages') WHERE name='$key'";
	$results = $dbh->prepare($sql);
	$results->execute();
	$column = $results->fetch(PDO::FETCH_ASSOC);

	// if( array_key_exists("pages", $GLOBALS['db_schema']) && is_array($columns) && !in_array($key, $columns) ){
	if( $column["OK"] == 0 ){
		$sql = "ALTER TABLE pages ADD COLUMN ". $key;
		$results = $dbh->prepare($sql);
		if( $results ) $results->execute(); // there's a case where the column may already exist, in which case this will be false...
		array_push( $columns, $key ); // obsolete?
	}

	// get existing page (again?)
	$sql = "SELECT * FROM 'pages' WHERE id='$id'";
	$results = $dbh->prepare($sql);
	if( $results ){
		$results->execute();
		$pages = $results->fetch(PDO::FETCH_ASSOC);
	} else {
		$pages = false;
	}
	// just create the key
	if( !$pages ) {
		$newpage = new Page();
		$newpage->set('id', "$id");
		if($key)
			$newpage->set("$key", "$value");
		$newpage->create();

	} else {
		if($key){
			$mypage = new Page($id);
			$existing = $mypage->get("$key");
			// allow empty strings to be returned
			if( is_null($existing) ){
				$mypage->set("$key", "$value");
				$mypage->update();
			}
		}
	}

	}

}
?>
