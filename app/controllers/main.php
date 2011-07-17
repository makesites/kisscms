<?php

class Main extends Controller {

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
	
		$this->data['view'] = getPath('views/main.php');

		// get the page details stored in the database
		$this->requestPage( $this->data );
		
		// add aditional information if this is the admin
		if (isset($_SESSION['admin'])) {
			$this->data['body']['admin']= View::do_fetch( getPath('views/admin/topbar.php'), $this->data);
		}

		// load the view for the page content
		$this->data['body'][]= View::do_fetch($this->data['view'], $this->data);
		$this->data['head'] = array();
		$this->data['aside'] = array();
		// fallback to the default template if the template isn't available
		$template =(array_key_exists('template', $this->data) && is_file(TEMPLATES.$this->data['template'])) ? TEMPLATES.$this->data['template'] : TEMPLATES.DEFAULT_TEMPLATE;
		View::do_dump($template,$this->data);
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