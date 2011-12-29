<?php

class Archives extends Controller {

	public $data;
	public $date;
	
	//This function maps the controller name and function name to the file location of the .php file to include
	function index( $path ) {
		
		$this->data['date'] = implode("-", $path);
		
		// load the index
		$this->render();
	}

	function render() {
		
		// get the page details stored in the database
		$this->requestAllPages();
		
		// define the rendereing template
		$this->data['template']= LISTINGS_TEMPLATE;
		
		// display the page
		Template::output($this->data);
	}
	
	function requestAllPages() {

		$page=new Page();
		$page->tablename = "pages";
		$pages = $page->retrieve_many("date LIKE '%".$this->date."%'");
		$view = getPath('views/archives/body.php');
		
		foreach( $pages as $data ){
			$data['view'] = $view;
			$this->data['body'][] = $data;
		}

	}
	

}


?>