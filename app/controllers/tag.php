<?php

class Tag extends Controller {

	public $data;
	public $tag;
	
	//This function maps the controller name and function name to the file location of the .php file to include
	function route_request() {
		
		// the main controler is a special case that has only one parameter - the url
		$this->data['path'] = preg_replace('#^'.addslashes(WEB_FOLDER).'#', '', $_SERVER['REQUEST_URI']);
		// check if we have a trailing slash (and remove it) 
		$this->data['path'] = ( substr($this->data['path'], -1) == "/" ) ? substr($this->data['path'], 0, -1) : $this->data['path'];
		
		$this->tag = $this->getTag();
		
		// load the index
		$this->render();
		
		return $this;
	}

	function render() {
		
		// get the page details stored in the database
		$this->requestAllPages();
		
		// add the config in the data object
		$this->data['config'] = $GLOBALS['config'];
		
		//$this->data['body']['main']= View::do_fetch( getPath('views/tag/body.php'), $this->data);
		// display the page
		Template::output($this->data);
	}
	
	function requestAllPages() {

		$page=new Page();
		$page->tablename = "pages";
		$pages = $page->retrieve_many("tags like '%".$this->tag."%'");
		$view = getPath('views/tag/body.php');
		
		foreach( $pages as $p ){
			$data = $p->rs;
			$data['view'] = $view;
			$this->data['body'][] = $data;
		}

	}
	
	
	function getTag() {
		$params = explode("/", $this->data['path']);
		// path like : tag/name
		return $params[1];
	}
	

}


?>