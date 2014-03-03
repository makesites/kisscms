<?php

if( !class_exists("KISS_Auth") ){

class KISS_Auth extends Controller {

	protected $api = array();

	// Generic logout method
	function logout() {

		// remove cookie(s)
		foreach($_COOKIE as $name=>$value){
			unset($_COOKIE[$name]);
			setcookie($name, NULL, -1);
		}

		// exit from each API (if available)
		foreach( $this->api as $api){
			if(method_exists($api,'logout')) $api->logout();
		}

		// destroy main session
		session_destroy();

		// reload the site
		header("Location: ". url());

	}


	// Helpers

	// this function checks the user against a service (we assume there is an id)
	function getUser($api=false){
		// convention: lookup for a 'user' object in the db array
		$db= ( !empty($this->db['user']) ) ? $this->db['user'] : false;

		// STEP 1: find the user id
		if( !empty($_SESSION['user']['id']) ){
			// if there is a session we are ok
			$id = $_SESSION['user']['id'];
			// check last accessed
			$accessed = ( !empty($_SESSION['user']['updated']) ) ? ( strtotime("now") - (int)$_SESSION['user']['updated'] ) : false;
			// it's been less than 2 min so just return the session model (to avoid latency from constant db/api requests)
			if( $accessed && $accessed < 120 && !DEBUG ) return $_SESSION['user'];

		} else if( $db && $api && $api->login() ) {
			// the service has found a valid login
			// lookup info from the remote service
			$me = $api->me();
			$accounts = $db->get("accounts");
			if( is_null( $accounts ) ){
				// assume that api->id == id
				$id = $me['id'];
			} else {
				try {
					$result = $db->findOne("accounts", array($api->name, $me['id']) );
					if( !empty( $result ) ) {
						$id = $db->get("id");
						$synced = true; // this should become a flag of the base Model class
					} else {
						// legacy api (remove soon)
						$id = $me['id'];
					}
				} catch( Exception $e ){
					// legacy api (remove soon)
					$id = $me['id'];
				}
			}

		} else if( !empty( $_COOKIE['user'] ) ) {
			// empty user session AND oauth token
			// lookup the client cookie (as a last resort)
			$id = $_COOKIE['user'];
		}

		// Final exit for non members...
		// if no id -> assume the user is not logged in
		// if $me['id'] exists it means an api connection has been made..
		if( !isset( $id ) && empty($me['id']) ) return false;
		// exit now if there is no db
		//if( !db ) return $id;

		// STEP 2: Read the user model
		// - check if there's a user in the DB
		// following logic needs to be conditioned in case the $db is not setup
		if( isset( $id ) && !isset($synced) ) $db->read( $id );

		// STEP 3: Update credentials
		// - update existing data with the remote service (not sure about this one...)
		if( isset( $me ) ) {
			// filter fields (use clone array?)
			unset($me["id"] );
			$db->merge( $me );
		}
		// - if logged in to the api assume it has the latest oauth info
		if( $api && $api->login() && array_key_exists('oauth', $_SESSION) ) {
			//
			//$db->set('oauth', $api->oauth->creds() );
			$db->set('oauth', $_SESSION['oauth'] );

		} else if( !is_null( $db->get('oauth') ) ) {
			// check for credentials that have expired?
			// use the creds from the db
			$oauth = (array) $db->get('oauth');
			if( is_array($oauth) && array_key_exists($api->name, $oauth) ){
				$api->oauth->creds( $oauth[ $api->name ] );
				// re-initiate service (to login to the api)
				$api->init();
			}
		}

		// - record the time
		//$db->set("updated", timestamp() );
		if( $api ) $db->extend("accounts", array( $api->name => $this->getAPIdetails( $api->me() ) ) );
		// STEP 4: SAVE back to the db
		// - update the user model (condition update only if oauth data has changed...)
		$method = ( !isset($id) ) ? "create" : "update";
		$db->{$method}();

		// - set the user cookie (expires in one day)
		//setcookie("user", $db->get("id"), time()+86400); // disabling temporarily (not working in 5.3)
		// - set the session
		$_SESSION['user'] = $db->getAll();

		return $_SESSION['user'];

	}

	function getAPIdetails( $data ){
		$details = array();

		$fields = array("id", "username", "email", "name");
		foreach( $fields as $field ){
			if( array_key_exists( $field, $data ) ) $details[$field] = $data[$field];
		}

		return $details;
	}

}
}

?>