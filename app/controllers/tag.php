<?php

class Tag extends Controller {

	public $data;
	public $tag;

	//This function maps the controller name and function name to the file location of the .php file to include
	function index( $params ) {

		// the main controler is a special case that has only one parameter - the url
		$this->data['path'] = preg_replace('#^'.addslashes(WEB_FOLDER).'#', '', $_SERVER['REQUEST_URI']);
		// check if we have a trailing slash (and remove it)
		$this->data['path'] = ( substr($this->data['path'], -1) == "/" ) ? substr($this->data['path'], 0, -1) : $this->data['path'];

		$this->tag = $params;

		// load the index
		$this->render();
	}

	function render( $view=false ) {

		// get the page details stored in the database
		$this->requestAllPages();

		// define the rendereing template
		if( !$this->data['template'] ) $this->data['template'] = LISTINGS_TEMPLATE;

		// display the page
		Template::output($this->data);
	}

	function requestAllPages() {

		$page=new Page();
		$page->tablename = "pages";
		$pages = $page->retrieve_many("tags like '%".$this->tag."%'");
		$view = getPath('views/tag/body.php');

		foreach( $pages as $data ){
			$data['view'] = $view;
			$this->data['body'][] = $data;
		}

	}

	public static function getBody( $path ) {
		// pls replace strng $path with proper params array (make parameter generation more abstruct)
		$items = array();
		$tag = preg_replace('#^tag/#', '', $path);

		$page=new Page();
		$page->tablename = "pages";
		$pages = $page->retrieve_many("tags like '%".$tag."%'");
		$view = getPath('views/tag/body.php');

		foreach( $pages as $data ){
			$data['view'] = $view;
			$items[] = $data;
		}

		return $items;
	}

}


?>