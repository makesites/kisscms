<?php

class Section {

	public $view;
	public $data = array();

	function __construct($view=false, $vars=false, $data=false){

		// defaults
		$defaults = array( 	'id' => false, 'class' => false,
							'delimiter' => false, 'weight' => false,
							'h3' => false,  'h4' => false, 'h5' => false,
							'h3-id' => false,'h3-class' => false,
							'h4-id' => false,'h4-class' => false,
							'h5-id' => false,'h5-class' => false,
							'ul' => false, 'ul-id' => false, 'ul-class' => false,
							'li' => false, 'li-id' => false, 'li-class' => false,
							'tag' => false, 'path' => false
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


	public static function view($view=false, $vars=false, $data=false, $class=false){
		// get the called class if not defined
		if(!$class){
			$class = get_called_class();
		}
		// 1st fallback - a view named as the section class name
		if(!$file = getPath('views/sections/'. $view .'.php')) $view  = strtolower( $class );
		// 2nd fallback - use the default view
		if(!$file = getPath('views/sections/'. $view .'.php')) $view  = 'default';

		$file = getPath('views/sections/'. $view .'.php');
		// save the view we found
		$view = $file;

		if( class_exists ( $class ) ){
			$section = new $class($view, $vars, $data);
		}
	}

	public static function ul($vars=false, $data=false){
		$view = 'ul';
		$class = get_called_class();
		Section::view($view, $vars, $data, $class);
	}


	public static function inline($vars=false, $data=false){
		$view = 'inline';
		$class = get_called_class();
		Section::view($view, $vars, $data, $class);
	}


	function createVars($vars=false){
		if(!$vars) return;
		// replace commas with carriage returns
		$search = array(", ", ":");
		$replace = array("\n", ": ");

		$vars = str_replace($search, $replace, $vars);

		$array = sfYaml::load($vars);

		return $array;

	}

	function render(){
		View::do_dump($this->view, $this->data);
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

}


class Menu extends Section {

	function __construct($view=false, $vars=false, $data=false){
		parent::__construct($view,$vars);
		$this->data['items'] = $this->getItems($data);
		$this->render();
	}

	private function getItems($data=false){
		$items = array();
		// fallback to menu tag if no tag is set
		$tag = ( $this->data['vars']['tag'] ) ? $this->data['vars']['tag'] : "menu";
		$uri = ( array_key_exists('PATH_INFO', $_SERVER) ) ? substr( $_SERVER['PATH_INFO'], 1) : "";

		if( array_key_exists('db_pages', $GLOBALS) ){
			$dbh = $GLOBALS['db_pages'];
			$sql = 'SELECT * FROM "pages"';
			if ($tag) {
				$sql .= ' WHERE tags LIKE "%'. $tag .'%"';
			}
			$sql .= ' ORDER BY "id"';
			$results = $dbh->query($sql);
			if( !$results ) return $items;
			while ($v = $results->fetch(PDO::FETCH_ASSOC)) {
				// pick only first level pages
				$path = explode("/", $v['path'] );
				// get the position of the item, if any
				preg_match("/".$tag."[a-z\-]*-(\d+)/", $v['tags'], $order );
				$order = array_pop( $order );
				$k = ( is_null( $order ) ) ? false : (int) $order -1;
				$selected = ($uri == $v['path']) ? "selected" : null;

				$p = (count($path) > 1) ? $path[0] : $v['path'];
				$title = (count($path) > 1) ? ucwords($path[0]) : $v['title'];

				$item = array( 'url' =>  url( $p ), 'title' => $title );
				// add seleted class if uri matches path
				$item['selected'] = $selected;
				$item['order'] = $k;
				// position in their right order
				if( $k !== false ){
					if( array_key_exists($k, $items) ){
						$i = $items[$k];
						$items[$k] = $item;
						$items[] = $i;
					} else {
						$items[$k] = $item;
					}
				} else {
					$placed = false;
					for( $j = 0; $j < count( $items ); $j++){
						if( !array_key_exists($j, $items) ){
							$items[$j] = $item;
							$placed = true;
						}
					}
					// place at the end if not placed
					if( !$placed ) $items[] = $item;
				}
			}
		}
		// sort elements by key
		ksort($items);
		return $items;
	}

}


class Breadcrumb extends Section {

	function __construct($view=false, $vars=false, $data=false){
		$this->data['items'] = $this->getItems($data);
		parent::__construct($view,$vars);
		$this->render();
	}

	private function getItems(){
		$items = array();
		$path = explode("/", $GLOBALS['path']);
		//$title = $GLOBALS['title'];
		// always include the homepage
		$items[] = array("url"=> url(), "title" => "Home" );

		foreach($path as $dir){
			$items[] = array("url"=> url( implode("/", $path) ), "title" => ucwords(end($path)));
			array_pop($path);
		}
		return $items;
	}

}


class Tags extends Section {

	function __construct($view=false, $vars=false, $data=false){
		// form data
		$this->data['items'] = $this->getItems($data);
		parent::__construct($view, $vars, $data);
		$this->render();
	}

	private function getItems($data=false){
		// create an array if we are provided with a comma delimited list
		$items = array();
		$tags = array();

		if( !$data ) {
			// get the full list of tags
			if( array_key_exists('db_pages', $GLOBALS) ){
				$dbh = $GLOBALS['db_pages'];
				$sql = 'SELECT tags FROM "pages" ORDER BY "date"';
				$results = $dbh->query($sql);

				while ($v = $results->fetch(PDO::FETCH_ASSOC)) {
					$v_tags = explode(",", $v['tags']);
					foreach($v_tags as $tag){
						$tags[] = $tag;
					}
				}
			}
		} else {
			$tags = (!is_array($data)) ? explode(",",$data) : $data;
		}

		//$filter = array( if( $item['title'] != "" && strpos( $item['title'], "menu-" ) == false && $item['title'] != "category" ){
		// form the array in items format
		foreach($tags as $k=>$tag){
			// filter out specific tags
			// - empty tags
			// - tags that start with "_"
			// - tags that start with "menu-"
			// - tags that start with "author-"
			// - specific tags: "category"...
			if ( preg_match("/^$|^_|^menu-|^author-|^category$/", $tag) ) continue;

			// calculate the weight
			if(array_key_exists($tag, $items)){
				$items[$tag]['weight'] += 1;
			} else {
				$items[$tag] = array( 'url' =>  url( "tag/".$tag, true ), 'title' => $tag, 'weight' => 1 );
			}
		}

		return $items;
	}

	public static function cloud($vars=false, $data=false){
		// set the view
		$view = 'tagcloud';
		$class = get_called_class();
		// process the view
		Section::view($view, $vars, $data, $class);
	}

}


class Pagination extends Section {

	function __construct($view=false, $vars=false, $data=false){
		parent::__construct($view,$vars);
		$this->render();
	}

}


class Archive extends Section {

	function __construct($view=false, $vars=false, $data=false){
		// Additional defaults for specific section, if not set
		if(!strpos($vars, "h3"))
			$vars .= ", h3: 'Archives'";

		parent::__construct($view,$vars);

		if( array_key_exists('db_pages', $GLOBALS) ){
			$dbh = $GLOBALS['db_pages'];
			$sql = 'SELECT * FROM "pages" ORDER BY "date"';
			$results = $dbh->query($sql);
			while ($v = $results->fetch(PDO::FETCH_ASSOC)) {
				$date = strtotime($v['date']);
				$title = date("F Y", $date );
				$url = "archives/" . date("Y", $date ) ."/". date("m", $date ) ."/";
				//$date =  = date("Y", v() );
				$items[$title] = array( 'url' =>  url( $url ), 'title' => $title );
			}
		}
		$this->data['items'] = $items;
		$this->render();
	}

}


class Search extends Section {

	function __construct($view=false, $vars=false, $data=false){
		parent::__construct($view,$vars);
		$this->data['items'] = array();
		$this->render();
	}

}


class LatestUpdates extends Section {

	function __construct($view=false, $vars=false, $data=false){
		if(!strpos($vars, "h3"))
			$vars .= ", h3: 'Latest Updates'";
		parent::__construct($view,$vars);
		$this->data['items'] = $this->getItems();
		$this->render();
	}

	private function getItems(){
		$items = array();

		if( array_key_exists('db_pages', $GLOBALS) ){
			$dbh = $GLOBALS['db_pages'];
			$sql = 'SELECT * FROM "pages" ORDER BY "date" DESC LIMIT 10';
			$results = $dbh->query($sql);
			while ($v = $results->fetch(PDO::FETCH_ASSOC)) {
				$items[] = array( 'url' =>  url( $v['path'] ), 'title' => $v['title'] ." (". date("d-m-Y", strtotime($v['date'])) . ")" );
			}
		}
		return $items;
	}

}

// output the body section of any other page (provided that the relevant controller supports the getBody() method)
class Body extends Section {

	function __construct($view=false, $vars=false, $data=false){
		parent::__construct($view,$vars);
		// find the requested controller from the path
		$class = findController( $this->data["vars"]["path"] );
		// get the data for the specific URI
		$this->data['items'] = $class::getBody($this->data["vars"]["path"]);
		// currently override the view set by the parent section class
		//$this->view = ( $view ) ? getPath('views/'.$class.'/body-'.$view.'.php') : getPath('views/'.$class.'/body.php');
		$this->view = getPath('views/'. strtolower($class).'/body.php');
		// render the section
		$this->render();
	}

	// override default rendering method to render each piec eof content with their respective view...
	function render(){
		// check if we have defined a view
		foreach( $this->data['items'] as $item){
			$view = ( !$this->view ) ? $item['view'] : $this->view;
			View::do_dump($view, $item);
		}
	}
}

?>