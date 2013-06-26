<?php

if (!class_exists('REST_Service')){

class REST_Service extends Controller {

	protected $api;
	protected $model;
	protected $params;

	function index( $params ) {
		// by default no index available
		exit;
	}

	// this method displays a specific Task
	protected function crud( $params ) {
		// reset data
		$this->data = array();

		// redirect to the proper method
		switch($_SERVER['REQUEST_METHOD']){
			case "POST":
				$this->create( $params );
			break;
			case "GET":
				$this->read( $params );
			break;
			case "PUT":
				$this->update( $params );
			break;
			case "DELETE":
				$this->delete( $params );
			break;
			default:
				header('HTTP/1.1 405 Method Not Allowed');
		}

		// in any case, render the output
		$this->render();
	}

	protected function create( $params ) {
		// method is off limits for not logged in users...
		if( empty( $_SESSION['user'])  ) exit;

		$data = array();
		//for each db initiated...
		foreach( $this->db as $type => $db){
			$action = "create".ucfirst($type);
			if( method_exists($this, $action) ) {
				$result = $this->$action($params);
				// stop if we got a negative response
				if( !$result ) continue;
				// remove the parent array if only one dataset
				$data[$type] = ( count($result) == 1 ) ? array_shift($result) : $result;
			}
		}
		// remove the parent array if only one dataset
		$this->data = ( count($data) == 1 ) ? array_shift($data) : $data;
		// debug
		//error_log( print_r($this->data,1) , 3, "log.txt");
	}

	protected function read( $params ) {

		$data = array();
		//for each db initiated...
		foreach( $this->db as $type => $db){
			$action = "read".ucfirst($type);
			if( method_exists($this, $action) ) {
				$result = $this->$action($params);
				// stop if we got a negative response
				if( !$result ) continue;
				// remove the parent array if only one dataset
				$data[$type] = ( count($result) == 1 ) ? array_shift($result) : $result;
			}
		}
		// remove the parent array if only one dataset
		$this->data = ( count($data) == 1 ) ? array_shift($data) : $data;
		// debug
		//error_log( print_r($this->data,1) , 3, "log.txt");
	}

	protected function update( $params ) {
		// method is off limits for not logged in users...
		if( empty( $_SESSION['user'])  ) exit;

		$data = array();
		//for each db initiated...
		foreach( $this->db as $type => $db){
			$action = "update".ucfirst($type);
			if( method_exists($this, $action) ) {
				$result = $this->$action($params);
				// stop if we got a negative response
				if( !$result ) continue;
				// remove the parent array if only one dataset
				$data[$type] = ( count($result) == 1 ) ? array_shift($result) : $result;
			}
		}
		// remove the parent array if only one dataset
		$this->data = ( count($data) == 1 ) ? array_shift($data) : $data;
		// debug
		//error_log( print_r($this->data,1) , 3, "log.txt");
	}

	protected function delete( $params ) {
		// method is off limits for not logged in users...
		if( empty( $_SESSION['user'])  ) exit;

		$data = array();
		//for each db initiated...
		foreach( $this->db as $type => $db){
			$action = "delete".ucfirst($type);
			if( method_exists($this, $action) ) {
				$result = $this->$action($params);
				// stop if we got a negative response
				if( !$result ) continue;
				// remove the parent array if only one dataset
				$data[$type] = ( count($result) == 1 ) ? array_shift($result) : $result;
			}
		}
		// remove the parent array if only one dataset
		$this->data = ( count($data) == 1 ) ? array_shift($data) : $data;
		// debug
		//error_log( print_r($this->data,1) , 3, "log.txt");
	}


	function render() {

		// set the right header
		if (isset($_SERVER['HTTP_ACCEPT']) &&
			(strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
			header('Content-type: application/json');
		} else {
			header('Content-type: text/plain');
		}

		// display the data in json format
		View::do_dump( getPath('views/main/json.php'), $this->data );
	}

	// Helpers
	// find the id from the params array (not setting if not available)
	protected function findID($params){

		if( !$params || empty($params) ){
			// reset params
			$params = array();
		} else if( is_string($params) ){
			// we assume the only param is the id
			$id = $params;
			// reset params
			$params = array();
			$params['id'] = $id;
		} else if( empty($params['id']) ) {
			// reset the index of the params
			reset($params);
			//if the first key is '0' we assume it is an id sent as part of the url
			if ( !key($params) ) $params['id'] = array_shift($params);
		}

		// save for later...
		$this->params = $params;
		return $params;
	}

}

}

?>