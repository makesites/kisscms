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

function showContent( $content ){

	if (isset($content) && is_array($content))
	  foreach ($content as $html)
		echo "$html\n";

}

function head($head, $cms_styles){

	if(isset($cms_styles)){ ?>
		<link href="<?=WEB_DOMAIN?>/assets/css/admin/main.css" rel="stylesheet" type="text/css" media="screen" />
		<link href="<?=WEB_DOMAIN?>/assets/css/admin/jquery.ui.autocomplete.custom.css" rel="stylesheet" type="text/css"  />
	<?php } ?>
	
	<?php
	if (isset($head) && is_array($head))
	  foreach ($head as $html)
		echo "$html\n";

}

?>