<?php

class Archives extends Controller {

	public $data;
	public $date;

	//This function maps the controller name and function name to the file location of the .php file to include
	function index( $params ) {

		// data is being passed as a key-value pair
		foreach( $params as $k => $v ){
			$this->data['date'] = $k ."-". $v;
			// only loop throught he first item
			break;
		}

		// load the index
		$this->render();
	}

	function render( $view=false ) {

		// get the page details stored in the database
		$this->requestAllPages();

		// define the rendering template
		if( !$this->data['template'] ) $this->data['template'] = LISTINGS_TEMPLATE;

		// display the page
		Template::output($this->data);
	}

	function requestAllPages() {

		$page=new Page();
		$page->tablename = "pages";
		$pages = $page->retrieve_many("date LIKE '%".$this->data['date']."%'");
		$view = getPath('views/archives/body.php');

		foreach( $pages as $data ){
			$data['view'] = $view;
			$this->data['body'][] = $data;
		}

	}


}


?>