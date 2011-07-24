<?php

class Section {
	
	public $view;
	public $data = array();
	
	function __construct($view=false, $vars=false){		
		if( $view )
			$this->view = $view;
		if( $vars )
			$this->data = json_decode( $vars, true);
	}
	
	static function display($section='', $vars=false, $view=false){
		$class =  ucwords($section);
		if( class_exists ( $class ) ){ 
			new $class($view, $vars);
		}
	}
	
	
	function render(){
		// if there is a view, use it
		if($this->view) { 
			$file = getPath('views/sections/'. $this->view .'.php');
		// else if there is a view in the sections folder with the same name as the class, use it
		} else { 
			$view = strtolower( get_class ( $this ) );
			$file = getPath('views/sections/'. $view .'.php');
			// finally, try to render the section with the default view
			if(!$file)
				$file = getPath('views/sections/default.php');
		}
		View::do_dump($file, $this->data);
	}
	
}


class Copyright extends Section {
	
	function __construct($view=false, $vars=false){
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
	
}


class Menu extends Section { 

	function __construct($view=false, $vars=false){
		parent::__construct($view,$vars);
		$pages=array();
		if( array_key_exists('db_pages', $GLOBALS) ){
			$dbh = $GLOBALS['db_pages'];
			$sql = 'SELECT * FROM "pages" ORDER BY "date"';
			$results = $dbh->query($sql);
			while ($v = $results->fetch(PDO::FETCH_ASSOC)) {
				$pages[] = array( 'url' =>  myUrl( $v['path'], true ), 'title' => $v['title'] );
			} 
		}
		$this->data['pages'] = $pages;
		$this->render();
	}
}


class Breadcrumb extends Section {
	
	function __construct($view=false, $vars=false){
		parent::__construct($view,$vars);
		
	}
	
}



class Archive extends Section {
	
	function __construct($view=false, $vars=false){
		parent::__construct($view,$vars);
				echo <<<HTML
		
							<h4>Archives</h4>		<ul>
			<li><a href="/2011/03/" title="March 2011">March 2011</a></li>
	<li><a href="/2010/03/" title="March 2010">March 2010</a></li>
	<li><a href="/2010/01/" title="January 2010">January 2010</a></li>
	<li><a href="/2009/11/" title="November 2009">November 2009</a></li>
	<li><a href="/2009/10/" title="October 2009">October 2009</a></li>
	<li><a href="/2009/06/" title="June 2009">June 2009</a></li>
	<li><a href="/2009/05/" title="May 2009">May 2009</a></li>
	<li><a href="/2009/04/" title="April 2009">April 2009</a></li>
	<li><a href="/2009/03/" title="March 2009">March 2009</a></li>
	<li><a href="/2009/02/" title="February 2009">February 2009</a></li>
	<li><a href="/2009/01/" title="January 2009">January 2009</a></li>
	<li><a href="/2008/10/" title="October 2008">October 2008</a></li>
		</ul>
		
HTML;

	}
	
}


?>