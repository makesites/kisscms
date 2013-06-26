<?php

if (!class_exists('Session')){

class Session extends REST_Service {

	function index( $params=array() ){
		// enable cors
		$this->cors();
		// re-route to the right method
		parent::crud( $params );
	}

	protected function create( $params=array() ) {
		// filter submitted data?

		// is create just an update (with no assigned id)?
		$this->update( $params );

		// generate id...

		// render
		$this->read();

	}

	protected function read( $params=array() ) {

		// the core of the data is the user object
		$auth = ( !empty($_SESSION['user'] ) );
		// filter session (fixed fields for now...)
		if( $auth ) $this->data['user'] = $this->filter( $_SESSION['user'] );
		// loop through the token data
		if( !empty($_SESSION['oauth']) ){
			$this->data['oauth'] = array();
			foreach( $_SESSION['oauth'] as $service => $creds){
				// save in an array if more than one...
				$this->data["oauth"][$service] = $this->filter( $creds );
			}
		}
		// set the auth flag
		$this->data['auth'] = $auth;
		// set updated attribute (for the client)
		$this->data['updated'] = time();

	}

	protected function update( $params=array() ) {

		// set the used namespace
		if( !array_key_exists("user", $_SESSION) || !is_array($_SESSION['user'] ) ) $_SESSION['user'] = array();
		if( !array_key_exists("oauth", $_SESSION) || !is_array($_SESSION['oauth'] ) ) $_SESSION['oauth'] = array();

		// FIX - force object to become an assosiative array
		$params = json_decode(json_encode($params), true);

		// validate first?
		if( array_key_exists('user', $params) )
			$_SESSION['user'] = array_merge( $_SESSION['user'], $params['user']);
		if( array_key_exists('oauth', $params) )
			$_SESSION['oauth'] = array_merge( $_SESSION['oauth'], $params['oauth']);

		// render the final data
		$this->read();
	}

	protected function delete( $params=array() ) {
		// reset user session
		unset( $_SESSION['user'] );
		// render
		$this->read();
	}

	protected function filter( $data=array() ) {
		// filter certain keywords
		unset( $data['password'] );
		unset( $data['secret'] );
		unset( $data['oauth_token_secret'] );

		return $data;
	}

}

}

?>