<?php

class Sitemap {

	public $data;

	function __construct(){
		$this->data['items'] = $this->getItems();
		$this->render();
	}

	private function getItems(){
		$items = array();

		if( array_key_exists('db_pages', $GLOBALS) ){
			$dbh = $GLOBALS['db_pages'];
			$sql = 'SELECT * FROM "pages" ORDER BY "date" DESC';
			$results = $dbh->query($sql);
			while ($v = $results->fetch(PDO::FETCH_ASSOC)) {
				$url = $this->makeUrlString( $v );
				$date = $this->makeIso8601TimeStamp( $v );
				$frequency = $this->getFrequency( $v);
				$priority = $this->getPriority( $v );
				$items[] = array( 'url' =>  $url, 'date' => $date, 'frequency' => $frequency, 'priority' => $priority );
			}
		}
		return $items;
	}

	function makeUrlString($item) {
		$url = htmlentities( url( $item['path'] ), ENT_QUOTES, 'UTF-8');
		return $url;
	}

	function makeIso8601TimeStamp($item) {
		$dateTime = $item['date'];

		if (is_numeric(substr($dateTime, 11, 1))) {
			$isoTS = substr($dateTime, 0, 10) ."T"
					 .substr($dateTime, 11, 8) ."+00:00";
		}
		else {
			$isoTS = substr($dateTime, 0, 10);
		}
		return $isoTS;
	}

	function getFrequency( $item ) {

		$now = date('Y-m-d H:i:s');
		$last_update = $item['date'];
		// a precaution due to server timezone differences
		if( strtotime( $last_update ) >= strtotime( $now ) )
		{
			$now = $last_update;
		}

		$diff = get_time_difference( $last_update, $now );

		if($diff['days'] > 365){
			$frequency = 'yearly';
		}elseif($diff['days'] > 30){
			$frequency = 'monthly';
		}elseif($diff['days'] > 7){
			$frequency = 'weekly';
		}elseif($diff['days'] > 0){
			$frequency = 'daily';
		}elseif($diff['days'] == 0 && $diff['hours'] != 0){
			$frequency = 'hourly';
		}elseif($diff['days'] == 0 && $diff['hours'] == 0){
			$frequency = 'always';
		} else {
			$frequency = 'never';
		}

		return $frequency;
	}

	function getPriority( $item ) {
		$path = explode("/", $item['path'] );
		// calculate a number from 0 to 1, based on the tree structure
		$priority = 1 - (count($path) -1);

		return $priority;
	}

	function render(){

		$output = View::do_fetch( getPath('views/main/sitemap.php'), $this->data);
		// write the sitemap
		writeFile(APP.'public/sitemap.xml', $output, 'w');
		// write the compressed sitemap
		writeFile(APP.'public/sitemap.xml.gz', $output, 'w9');

		// view the Sitemap XML
		//header('Location: ./sitemap.xml');
	}

}

?>