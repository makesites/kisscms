<?php

class Tag extends Controller {

	public $data;

	//This function maps the controller name and function name to the file location of the .php file to include
	function route_request() {
		
		// the main controler is a special case that has only one parameter - the url
		$this->data['path'] = preg_replace('#^'.addslashes(WEB_FOLDER).'#', '', $_SERVER['REQUEST_URI']);
		// check if we have a trailing slash (and remove it) 
		$this->data['path'] = ( substr($this->data['path'], -1) == "/" ) ? substr($this->data['path'], 0, -1) : $this->data['path'];
		
		// load the index
		$this->render();
		
		return $this;
	}

	function render() {
	
		// get the page details stored in the database
		$this->requestPage( $this->data );
		
		// add the config in the data object
		$this->data['config'] = $GLOBALS['config'];
		
		$this->data['body']['main']= View::do_fetch( getPath('views/main/body.php'), $this->data);
		
		// display the page
		Template::output($this->data);
	}
	
	function requestPage( ) {

		$page=new Page();
		$page->get_page_from_path($this->data['path']);

		// see if we have found a page
		if( $page->get('id') ){
			// store the information of the page
			$this->data['id'] = $page->get('id');
			$this->data['title'] = stripslashes( $page->get('title') );
			$this->data['content'] = stripslashes( $page->get('content') );
			$this->data['tags'] = stripslashes( $page->get('tags') );
			$this->data['template'] = stripslashes( $page->get('template') );
		} else {
			// forward to create a new page
			$this->data['status']="new";
			$this->data['view']= getPath('views/admin/confirm_new.php');
		}

	}

}


?>