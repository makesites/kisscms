<?php

function mainMenu(){
	global $data;
    $dbh = getdbh();
	$sql = 'SELECT * FROM "pages" ORDER BY "date" LIMIT 5';
    $results = $dbh->query($sql);
	while ($variable = $results->fetch(PDO::FETCH_ASSOC)) {
		$data['modules']['latest_updates'][] = array("title"=>$variable['title'], "path"=>$variable['path']);
	};  
	viewDump("fragments/modules/main_menu.php", $data);
}


?>