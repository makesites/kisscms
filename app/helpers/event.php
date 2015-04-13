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

	public static function trigger( $event=false, &$data=array() ){
		// prerequisites
		if( !$event ) return;
		if( !array_key_exists('events', $GLOBALS) ) return;
		if( !array_key_exists($event, $GLOBALS['events']) ) return;
		//
		$classes = $GLOBALS['events'][$event];

		// convention: remove prefix- from event to reveal action
		$action = ( strpos($event, ':') ) ? substr( $event, strpos($event, ':')+1 ): $event;

		foreach( $classes as $class ){
			$class::$action( $data );
		}

	}

}

?>