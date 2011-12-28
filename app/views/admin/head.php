<?php if( (defined("CONTROLLER") && CONTROLLER=="admin") || (array_key_exists('admin', $_SESSION) && $_SESSION['admin'] ) ){ ?>
	<link href="<?=url('/css/admin.css')?>" rel="stylesheet" type="text/css" media="screen" />
	<link href="<?=url('/css/jquery.ui.autocomplete.custom.css')?>" rel="stylesheet" type="text/css"  />
<?php } ?>