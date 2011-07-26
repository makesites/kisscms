<?php

class Section {
	
	public $view;
	public $data = array();
	
	function __construct($vars=false, $view=false){
				
		$defaults = array( 'id' => '', 'class' => '', 
							'h3' => array( 'id' => '', 'class' => '', 'html' => ''),
							'ul' => array( 'id' => '', 'class' => ''),
							'li' => array( 'id' => '', 'class' => '', 'html' => '') 
						);
		// parse the passed variables 
		if( $view )
			$this->view = $view;
		$properties = json_decode( $vars, true);
		if( is_array( $properties ) )
			$this->data = array_merge( $defaults, $properties );
		else 
			$this->data = $defaults;
	}
	
	static function display($section='', $vars=false, $view=false){
		$class =  ucwords($section);
		if( class_exists ( $class ) ){ 
			new $class($vars, $view);
		}
	}
	
	
	function render(){
		// if there is a view, use it
		$class = strtolower( get_class ( $this ) );
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
		View::do_dump($file, $this->data);
	}
	
}


class Copyright extends Section {
	
	function __construct($vars=false, $view=false){
		parent::__construct($vars,$view);
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
	
}


class Menu extends Section { 

	function __construct($vars=false, $view=false){
		parent::__construct($vars,$view);
		
		$items = array();
		
		if( array_key_exists('db_pages', $GLOBALS) ){
			$dbh = $GLOBALS['db_pages'];
			$sql = 'SELECT * FROM "pages" ORDER BY "date"';
			$results = $dbh->query($sql);
			while ($v = $results->fetch(PDO::FETCH_ASSOC)) {
				$items[] = array( 'url' =>  myUrl( $v['path'], true ), 'title' => $v['title'] );
			} 
		}
		$this->data['li']['html'] = $items;
		$this->render();
	}
}


class Breadcrumb extends Section {
	
	function __construct($vars=false, $view=false){
		parent::__construct($vars,$view);
		
	}
	
}


class Tags extends Section {
	
	function __construct($vars=false, $view=false){
		// extra manipulation of the vars for this section
		$data['tags'] = explode(",", $vars);
		// encode to jsonc to decode again in the constrructor? there must be a better way...
		$vars = json_encode( $data );
		parent::__construct($vars,$view);
		$this->render();
	}
	
}


class Pagination extends Section {
	
	function __construct($vars=false, $view=false){
		parent::__construct($vars,$view);
		$this->render();
	}
	
}


class Archive extends Section {
	
	function __construct($vars=false, $view=false){
		parent::__construct($vars,$view);
		// Additional defaults for specific section
		$this->data['h3']['html'] = "Archives";
		
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
		$this->data['li']['html'] = $items;
		$this->render();
	}
	
}


class Search extends Section {
	
	function __construct($vars=false, $view=false){
		parent::__construct($vars,$view);
		$this->render();
	}
	
}

?>