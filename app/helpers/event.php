<?php

if( !class_exists('Event') ){

class Event {

	/* Events */
	public static function on( $event=false, $class=false ){
		// prerequisites
		if( !$event || !$class ) return;
		//
		if( !array_key_exists('events', $GLOBALS) ) $GLOBALS['events'] = array();
		if( !array_key_exists($event, $GLOBALS['events']) ) $GLOBALS['events'][$event] = array();
		//
		$GLOBALS['events'][$event][] = $class;
	}

	public static function trigger( $event=false, &$vars=array() ){
		// prerequisites
		if( !$event ) return;
		if( !array_key_exists('events', $GLOBALS) ) return;
		if( !array_key_exists($event, $GLOBALS['events']) ) return;
		// variables
		$numargs = func_num_args();
		$classes = $GLOBALS['events'][$event];
		//$arg_list = func_get_args();

		// convention: remove prefix- from event to reveal action
		$action = ( strpos($event, ':') ) ? substr( $event, strpos($event, ':')+1 ): $event;
		$group = str_replace(":".$action, "", $event); // what's left is the group...

		foreach( $classes as $class ){
			// first check if we passed an object reference
			if( is_a($class, ucwords($group)) ) {
				// find method
				if( method_exists($class, $action) ){
					$method = $action;
				} else if( method_exists($class, "on".ucwords($action)) ){
					$method = "on".ucwords($action);
				} else {
					continue;
				}
				// supporting second argument
				if ($numargs == 3) {
					$class->$method( $vars, func_get_arg(2) );
				} else {
					$class->$method( $vars );
				}
			} else {
				// static methods...
				// supporting second argument
				if ($numargs == 3) {
					$class::$action( $vars, func_get_arg(2) );
				} else {
					$class::$action( $vars );
				}
			}
		}

	}

}

}
?>