<?php

class Main extends Controller {

	public $data;
	
	//This function maps the controller name and function name to the file location of the .php file to include
	function index( $params ) {
		
		// the main controler is a special case that has only one parameter - the url
		$this->data['path'] = preg_replace('#^'.addslashes(WEB_FOLDER).'#', '', $_SERVER['REQUEST_URI']);
		// check if we have a trailing slash (and remove it) 
		$this->data['path'] = ( substr($this->data['path'], -1) == "/" ) ? substr($this->data['path'], 0, -1) : $this->data['path'];
		
		// load the index
		$this->render();

	}

	function render() {
	
		// get the page details stored in the database
		$is_page = $this->requestPage();
		// check if this is a category page
		if(!$is_page){
			$is_category = $this->requestCategoryPages();
			if(!$is_category){
				$this->requestNewPage();
			}
		}
		// add the config in the data object
		$this->data['config'] = $GLOBALS['config'];
		
		//$this->data['body']['main']= View::do_fetch( getPath('views/main/body.php'), $this->data);
		
		// display the page
		Template::output($this->data);
	}
	
	function requestPage( ) {
		
		$data = array();
		$page = new Page();
		$page->get_page_from_path($this->data['path']);
		
		// see if we have found a page
		if( $page->get('id') ){
			// store the information of the page
			$data['id'] = $this->data['id'] = $page->get('id');
			$data['title'] = stripslashes( $page->get('title') );
			$data['content'] = stripslashes( $page->get('content') );
			$data['tags'] = stripslashes( $page->get('tags') );
			$data['date'] = strtotime( stripslashes( $page->get('date') ) );
			
			$data['path']= $this->data['path'];
			$data['view'] = getPath('views/main/body.php');
			$this->data['body'][] = $data;
			$this->data['template'] = stripslashes( $page->get('template') );
			return true;
		} else {
			return false;			
		}
	}
	
	function requestCategoryPages() {

		$page=new Page();
		$page->tablename = "pages";
		$pages = $page->retrieve_many("path like '". $this->data['path'] ."%'");
		$view = getPath('views/main/category.php');
		
		if( count($pages) > 0 ){ 
			foreach( $pages as $p ){
				$data = $p->rs;
				$data['view'] = $view;
				$this->data['body'][] = $data;
			}
			return true;
		} else {
			return false;
		}

	}
	

	function requestNewPage( ) {
		// forward to create a new page
		$data['status']= $this->data['status']="new";
		$data['path']= $this->data['path'];
		$data['view']= getPath('views/admin/confirm_new.php');
		$this->data['body'][] = $data;
	}
	

}


?>