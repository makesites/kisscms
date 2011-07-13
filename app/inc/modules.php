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

function aside(){
	global $leftnav;
	
	print_R( $leftnav );
	
	if (isset($leftnav) && is_array($leftnav))
	  foreach ($leftnav as $blockhtml)
		echo "$blockhtml\n";

}

function content(){
	global $body;
	
	if (isset($body) && is_array($body))
	  foreach ($body as $blockhtml)
		echo "$blockhtml\n";
}

function head(){
	global $head, $cms_styles;
	
	if(isset($cms_styles)){ ?>
		<link href="<?=ASSETS_PATH?>css/admin/main.css" rel="stylesheet" type="text/css" media="screen" />
		<link href="<?=ASSETS_PATH?>css/admin/jquery.ui.autocomplete.custom.css" rel="stylesheet" type="text/css"  />
	<?php } ?>
	
	<?php
	if (isset($head) && is_array($head))
	  foreach ($head as $blockhtml)
		echo "$blockhtml\n";

}

?>