<?php

class Menu { 

	function display(){ 
		$data = array();
		
		if( array_key_exists('db_pages', $GLOBALS) ){
			$dbh = $GLOBALS['db_pages'];
			$sql = 'SELECT * FROM "pages" ORDER BY "date" LIMIT 5';
			$results = $dbh->query($sql);
			while ($v = $results->fetch(PDO::FETCH_ASSOC)) {
				$data['pages'][] = array( 'url' =>  myUrl( $v['path'], true ), 'title' => $v['title'] );
			} 
		}
		View::do_dump( getPath('views/main/menu.php'), $data);
	}
}

?>