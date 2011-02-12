<?php 
// create a fallback for the variables we are using
$cms_username = (!isset($cms_username)) ? '' : $cms_username; 
?>

<h2>Configuration</h2>

<form class="cms-form clearfix" method="post" action="<?=myUrl('cms/config/save')?>">
<?php 
	foreach( $GLOBALS['config'] as $k=>$v ){
		echo '<label>' . $GLOBALS['language']['config'][$k] . ':</label><input type="text" name="' . $k . '" value="' . $v . '" />' . "\n";
	
	}
?>
	<input type="submit" name="submit" class="button" value="Save" />
</form>