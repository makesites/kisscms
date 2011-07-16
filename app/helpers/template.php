<?php

function mainMenu(){

	if($GLOBALS['db_pages']){
		$dbh = $GLOBALS['db_pages'];
		$sql = 'SELECT * FROM "pages" ORDER BY "date" LIMIT 5';
		$results = $dbh->query($sql);
		while ($variable = $results->fetch(PDO::FETCH_ASSOC)) {
			$data['modules']['main_menu'][] = array("title"=>$variable['title'], "path"=>$variable['path']);
		};  
		View::do_dump( getPath('views/modules/main_menu.php'), $data);
	}
}

function showContent( $content ){

	if (isset($content) && is_array($content))
	  foreach ($content as $html)
		echo "$html\n";

}

function head($head, $cms_styles){

	if(isset($cms_styles)){ ?>
		<link href="<?=getURL('')?>/css/admin.css" rel="stylesheet" type="text/css" media="screen" />
		<link href="<?=getURL('')?>/css/jquery.ui.autocomplete.custom.css" rel="stylesheet" type="text/css"  />
	<?php } ?>
	
	<?php
	if (isset($head) && is_array($head))
	  foreach ($head as $html)
		echo "$html\n";

}

function listTemplates( $selected=null){
	
	$data['selected']['list_templates'] = $selected;
	
	if ($handle = opendir(TEMPLATES)) {
		while (false !== ($template = readdir($handle))) {
			if ($template == '.' || $template == '..') { 
			  continue; 
			} 
			if ( is_file(TEMPLATES.$template) ) {
				$data['modules']['list_templates'][] = $template;
			}
		}	
		View::do_dump( getPath('views/modules/list_templates.php'), $data);
	}
}

?>