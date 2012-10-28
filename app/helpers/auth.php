<?php

class KISS_Auth extends Controller {
	
	// Generic logout method
	function logout() {
		
		// remove cookie(s)
		foreach($_COOKIE as $name=>$value){
			unset($_COOKIE[$name]);
			setcookie($name, NULL, -1); 
		}
		
		// constrol API state(s)
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
		
		// just for testing - remove in prod
		unset( $_SESSION['user'] );
		
		if( !empty($_SESSION['user']['id']) ){
			// if there is a session we are ok
			$id = $_SESSION['user']['id'];
			// check last accessed 
			$accessed = strtotime("now") - strtotime( $_SESSION['user']['updated'] );
			// it's been less than 2 min so just return the session model (to avoid latency from constant db/api requests)
			if( $accessed < 120 ) return $_SESSION['user'];
			
		} else if( $api && $api->login() ) {
			// the service has found a valid login
			// lookup info from the remote service
			$me = $api->me();
			$id = $me['id'];
			
		} else if( !empty( $_COOKIE['user'] ) ) {
			// empty user session AND oauth token
			// lookup the client cookie (as a last resort)
			$id = $_COOKIE['user'];
		}
		
		// no id = assume the user is not logged in
		if( !isset( $id ) ) return false;
		
		// READ
		// - check if there's a user in the DB
		// following logic needs to be conditioned in case the $db is not setup
		$store = $db->read( $id );
		
		// UPDATE
		// - update with the remote serive (if called)
		if( isset( $me ) ) { 
			$db->merge( $me );
			// - add the latest oauth info
			$db->set('oauth', $api->oauth->creds() );
		} else {
			// use the creds from the db
			$api->oauth->creds( $db->get('oauth') );
			// re-initiate service
			$api->init();
		}
		
		// SAVE
		// - update the user model
		$method = ( !$store) ? "create" : "update";
		$db->{$method}();
		
		// - set the user cookie (expires in one day)
		setcookie("user", $id, time()+86400);
		// - set the session
		$_SESSION['user'] = $db->getAll();
		
		return $_SESSION['user'];
		
	}
	
	
}

?>