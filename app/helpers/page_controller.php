<?php

class Page_Controller extends KISS_Auth {
	protected $category = false;

	//This function maps the controller name and function name to the file location of the .php file to include
	function index( $params ) {

		// get the page details stored in the database
		$is_page = $this->getPage();
		$is_category = $this->getCategoryPages( $is_page );
		// check if this is a category page
		if(!$is_page && !$is_category){
			$this->getNewPage();
		}
		// FIX: add the new page link for category pages
		if(!$is_page && $is_category){
			$this->data['status']="new";
		}

		// render the page
		$this->render();
	}

	// - Helpers
	function getPage( ) {

		$data = array();
		// if there is no path, load the index
		if( empty( $this->data['path'] ) ){
			$page = new Page(1);
		} else {
			$page = new Page();
			$page->get_page_from_path($this->data['path']);
		}

		// see if we have found a page
		if( $page->get('id') ){
			// store the information of the page
			$data['id'] = $this->data['id'] = $page->get('id');
			$data['title'] = stripslashes( $page->get('title') );
			$data['content'] = stripslashes( $page->get('content') );
			$data['tags'] = stripslashes( $page->get('tags') );
			$data['date'] = strtotime( stripslashes( $page->get('date') ) );
			// check if the page has been classified as a category
			$this->category = ( strpos( $data['tags'], "category" ) > -1) ? true : false;

			$data['path']= $this->data['path'];
			//$data['view'] = getPath('views/main/body.php');
			$this->data['body'][] = $data;
			// FIX #38: copy data of "static" pages in a separate array for easy access
			$this->data['_page'] = $data;
			$this->data['template'] = stripslashes( $page->get('template') );
			return true;
		} else {
			return false;
		}

	}

	function getCategoryPages($is_page=false) {
		// a page needs the tag "category" to jump into this behaviour
		if( $is_page && !$this->category ) return false;

		$page=new Page();
		$page->tablename = "pages";

		// add a leading slash to the path
		$path = ( substr($this->data['path'], -1) == "/" ) ? $this->data['path'] : $this->data['path'].'/';

		$pages = $page->retrieve_many("path like '". $path ."%'");

		// FIX: reset the data (in case it has page info)
		if( $is_page ) $this->data['body'] = array();

		if( count($pages) > 0 ){
			foreach( $pages as $data ){
				$data['view'] = getPath('views/main/category.php');
				$this->data['body'][] = $data;
			}
			// everything checked out - ready to set the template
			$this->data['template'] = "category.php";

			return true;
		} else {
			return false;
		}

	}


	function getNewPage() {

		if( array_key_exists('admin', $_SESSION) && $_SESSION['admin'] ){
			// forward to create a new page
			$data['status']= $this->data['status']="new";
			$data['path']= $this->data['path'];
			$data['view']= getPath('views/admin/confirm_new.php');
			$this->data['body'][] = $data;
		} else {
			// show 404 error if not loggedin
			header("HTTP/1.0 404 Not Found");
			$data['view']= getPath('views/errors/404.php');
			$this->data['body'][] = $data;
		}

	}


}


?>