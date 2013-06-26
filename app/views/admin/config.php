<?php
// create a fallback for the variables we are using
$admin_username = (!isset($admin_username)) ? '' : $admin_username;
?>

<h2>Configuration</h2>

<form class="cms-form clearfix" method="post" action="<?=url('admin/config')?>">
<?php
	foreach( $GLOBALS['config'] as $controller=>$vars ){
		echo '<fieldset name="'.$controller.'">';
		echo '<legend><h3>'. ucwords( $controller ) .'</h3></legend>';
		foreach( $vars as $k=>$v ){
			// $GLOBALS['language']['config'][$k]
			echo '<label>' . ucwords( str_replace("_", " ", $k )) . ':</label><input type="text" name="'. $controller .'|' . $k . '"';
			// #25 - special presentation for password
			if( $k == "admin_password" ){
				echo ' value="" placeholder="type new password..." />' . "\n";
			} else {
				echo ' value="' . $v . '" />' . "\n";
			}
		}
		echo '</fieldset>';
	}
?>
	<input type="submit" name="submit" class="button" value="Save" />
</form>