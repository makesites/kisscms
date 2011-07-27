<?php

class Section {
	
	public $view;
	public $data = array();
	
	function __construct($view=false, $vars=false, $data=false){
		
		// defaults
		$defaults = array( 	'id' => false, 'class' => false, 
							'h3' => false,  'h4' => false, 'h5' => false,
							'h3-id' => false,'h3-class' => false,
							'h4-id' => false,'h4-class' => false,
							'h5-id' => false,'h5-class' => false,
							'ul' => false, 'ul-id' => false, 'ul-class' => false,
							'li' => false, 'li-id' => false, 'li-class' => false,
						);
		
		$this->view = $view;
		
		// parse the passed variables 
		$vars = $this->createVars($vars);
		if( is_array($vars) )
			$this->data['vars'] = array_merge( $defaults, $vars );
		else
			$this->data['vars'] = $defaults;
			
		return $this;
	}
	
	
	public static function display($view='default', $vars=false, $data=false){
		$class =  static::getSection();
		$view = getPath('views/sections/'. $view .'.php');
		
		if( class_exists ( $class ) ){ 
			$section = new $class($view, $vars, $data);
		}
	}
	
	public static function ul($vars=false, $data=false){
		$view = 'default';
		static::display($view, $vars, $data);
	}
	
	
	public static function inline($vars=false, $data=false){
		$view = 'inline';
		static::display($view, $vars, $data);
	}
	
	
	function createVars($vars=false){
		if(!$vars) return;
		// replace commas with carriage returns
		$search = array(", ", ",", ":");
		$replace = array(",", "\n", ": ");
		
		$vars = str_replace($search, $replace, $vars);
		
		$array = sfYaml::load($vars); 
		
		return $array;

	}
	
	function render(){
		// if there is a view, use it
		/*
		if($this->view) { 
			$file = getPath('views/sections/'. $this->view .'.php');
			// alternative naming for the view
			if(!$file)
				$file = getPath('views/sections/'. $class .'-'. $this->view .'.php');
		// else if there is a view in the sections folder with the same name as the class, use it
		} else { 
			$file = getPath('views/sections/'. $class .'.php');
			// finally, try to render the section with the default view
			if(!$file)
				$file = getPath('views/sections/default.php');
		}
		*/
		
		
		View::do_dump($this->view, $this->data);
	}
	
	public static function getSection(){
		return __CLASS__;
	}
	
}


class Copyright extends Section {
	
	function __construct($view=false, $vars=false, $data=false){
		parent::__construct($view,$vars);
		if( array_key_exists('db_config', $GLOBALS) ){
			// get site author
			$dbh = $GLOBALS['db_config'];
			$sql = 'SELECT value FROM "main" WHERE "key"="site_author"';
			$results = $dbh->query($sql);
			while ($v = $results->fetch(PDO::FETCH_ASSOC)) {
				$this->data['author'] = $v['value'];
			} 
			// get year
			$this->data['year'] = date("Y", time() );
			$this->render();
		}
	}
	
	public static function getSection(){
		return __CLASS__;
	}
}


class Menu extends Section { 

	function __construct($view=false, $vars=false, $data=false){
		parent::__construct($view,$vars);
		
		$items = array();
		
		if( array_key_exists('db_pages', $GLOBALS) ){
			$dbh = $GLOBALS['db_pages'];
			$sql = 'SELECT * FROM "pages" ORDER BY "date"';
			$results = $dbh->query($sql);
			while ($v = $results->fetch(PDO::FETCH_ASSOC)) {
				$items[] = array( 'url' =>  myUrl( $v['path'], true ), 'title' => $v['title'] );
			} 
		}
		$this->data['items'] = $items;
		$this->render();
	}
	
	public static function getSection(){
		return __CLASS__;
	}
}


class Breadcrumb extends Section {
	
	function __construct($view=false, $vars=false, $data=false){
		parent::__construct($view,$vars);
		$this->data['items'] = array();
		$this->render();
	}
	
	public static function getSection(){
		return __CLASS__;
	}
}


class Tags extends Section {
	
	function __construct($view=false, $vars=false, $data=false){
		// extra manipulation of the vars for this section
		parent::__construct($view, $vars, $data);
		$this->data['items'] = explode(",", $data);
		$this->render();
	}
	
	public static function getSection(){
		return __CLASS__;
	}
}


class Pagination extends Section {
	
	function __construct($view=false, $vars=false, $data=false){
		parent::__construct($view,$vars);
		$this->render();
	}
	
	public static function getSection(){
		return __CLASS__;
	}
}


class Archive extends Section {
	
	function __construct($view=false, $vars=false, $data=false){
		parent::__construct($view,$vars);
		// Additional defaults for specific section
		$this->data['vars']['h3'] = "Archives";
		
		if( array_key_exists('db_pages', $GLOBALS) ){
			$dbh = $GLOBALS['db_pages'];
			$sql = 'SELECT * FROM "pages" ORDER BY "date"';
			$results = $dbh->query($sql);
			while ($v = $results->fetch(PDO::FETCH_ASSOC)) {
				$date = strtotime($v['date']);
				$title = date("F Y", $date );
				$url = "archives/" . date("Y", $date ) ."/". date("m", $date ) ."/";
				//$date =  = date("Y", v() );
				$items[$title] = array( 'url' =>  myUrl( $url, true ), 'title' => $title );
			} 
		}
		$this->data['items'] = $items;
		$this->render();
	}
	
	public static function getSection(){
		return __CLASS__;
	}
}


class Search extends Section {
	
	function __construct($view=false, $vars=false, $data=false){
		parent::__construct($view,$vars);
		$this->data['items'] = array();
		$this->render();
	}
	
	public static function getSection(){
		return __CLASS__;
	}
}

?>