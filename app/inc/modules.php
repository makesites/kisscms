<?php

function mainMenu(){
	global $data;
    $dbh = getdbh( DB_PAGES );
	$sql = 'SELECT * FROM "pages" ORDER BY "date" LIMIT 5';
    $results = $dbh->query($sql);
	while ($variable = $results->fetch(PDO::FETCH_ASSOC)) {
		$data['modules']['latest_updates'][] = array("title"=>$variable['title'], "path"=>$variable['path']);
	};  
	View::do_dump( getPath('views/modules/main_menu.php'), $data);
}


?>