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
		// normalize parameters
		$params = $this->_normalize( $params );

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
			} else {
				$result = $this->createData($params, $type);
			}
			// stop if we got a negative response
			if( !$result ) continue;
			// remove the parent array if only one dataset (if nested)
			$data[$type] = ( count($result) == 1 && array_key_exists( 0, $result ) ) ? array_shift($result) : $result;
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
			} else {
				$result = $this->readData($params, $type);
			}
			// stop if we got a negative response
			if( !$result ) continue;
			// remove the parent array if only one dataset (if nested)
			$data[$type] = ( count($result) == 1 && array_key_exists( 0, $result ) ) ? array_shift($result) : $result;
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
			} else {
				$result = $this->updateData($params, $type);
			}
			// stop if we got a negative response
			if( !$result ) continue;
			// remove the parent array if only one dataset (if nested)
			$data[$type] = ( count($result) == 1 && array_key_exists( 0, $result ) ) ? array_shift($result) : $result;
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
			} else {
				$result = $this->deleteData($params, $type);
			}
			// stop if we got a negative response
			if( !$result ) continue;
			// remove the parent array if only one dataset (if nested)
			$data[$type] = ( count($result) == 1 && array_key_exists( 0, $result ) ) ? array_shift($result) : $result;
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
		// alias of normalize...
		return $this->_normalize($params);
	}

	protected function _normalize($params){
		//
		if( !$params || empty($params) ){
			// reset params
			$params = array();
		} else if( is_scalar($params) ){
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

	// Data methods
	protected function readData( $params=false, $key=false ) {

		// prerequisites
		if( empty($key) || !array_key_exists($key, $this->db) ) return;

		// store
		$db = $this->db[$key];

		//if(DEBUG) error_log($data["uid"], 3, "errors.log");

		// this is a very limited scope method, where we only return the data of the logged in user
		$query = array(
			"filters" => array( "uid" => $_SESSION['user']['id'], "updated" => "!null" ),
			"order" => "updated DESC"
		);

		// read item
		if( !empty($params['id']) ) $query["filters"]["id"] = $params['id'];

		//$this->data = $db->query( $query );
		//return $this->render();
		return $db->query( $query );

	}

	protected function createData( $params, $key=false ) {

		// prerequisites
		if( empty($params['id']) || empty($key) || !array_key_exists($key, $this->db) ) return;

		//if(DEBUG) error_log($data["uid"], 3, "errors.log");

		// merge existing data
		foreach($params as $key=>$value){
			if( !empty( $params[$key] ) && $key != "id") {
				$db->set($key, $params[$key]);
			}
		}
		// add extra fields
		$db->set('uid', $_SESSION['user']['id']);

		// save back to the db
		$result = $db->create();

		return $result;

	}

	protected function updateData( $params, $key=false ) {

		// prerequisites
		if( empty($params['id']) || empty($key) || !array_key_exists($key, $this->db) ) return;

		// read the entry first
		// pickup the page id from the params - use findID instead
		$db = $this->db[$key];
		// data
		$data = $db->read( $params['id'] );

		//if(DEBUG) error_log($data["uid"], 3, "errors.log");

		// DISABLED: allow update only for the owner (or the admins of the subject)
		//if($data["uid"] != $_SESSION['user']['id']) return false;

		// merge existing data
		foreach($params as $key=>$value){
			if( !empty( $params[$key] ) && $key != "id") {
				$db->set($key, $params[$key]);
			}
		}
		// save back to the db
		$result = $db->update();

		return $result;

	}

	protected function deleteData( $params, $key=false ) {

		// prerequisites
		if( empty($params['id']) || empty($key) || !array_key_exists($key, $this->db) ) return;

		// read the entry first
		// pickup the page id from the params - use findID instead
		$db = $this->db[$key];
		// data
		$data = $db->read( $params['id'] );

		//if(DEBUG) error_log($data["uid"], 3, "errors.log");

		// DISABLED: allow delete only for the owner (or the admins of the subject)
		//if($data["uid"] != $_SESSION['user']['id']) return false;

		// save back to the db
		$result = $db->delete();

		// return the model
		//if( $result ) $this->data = $data;
		return ( $result ) ? $data : false;

	}

}

}

?>