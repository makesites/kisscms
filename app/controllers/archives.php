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
		
		// add the config in the data object
		$this->data['config'] = $GLOBALS['config'];
		
		$this->data['status'] = 'archives';

		// display the page
		Template::output($this->data);
	}
	
	function requestAllPages() {

		$page=new Page();
		$page->tablename = "pages";
		$pages = $page->retrieve_many("date like '%".$this->date."%'");
		$view = getPath('views/archives/body.php');
		
		foreach( $pages as $p ){
			$data = $p->rs;
			$data['view'] = $view;
			$this->data['body'][] = $data;
		}

	}
	

}


?>