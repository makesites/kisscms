<?php

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

		foreach( $classes as $class ){
			// supporting second argument
			if ($numargs == 3) {
				$class::$action( $vars, func_get_arg(2) );
			} else {
				$class::$action( $vars );
			}
		}

	}

}

?>