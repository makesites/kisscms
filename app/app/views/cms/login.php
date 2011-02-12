<?php 
// create a fallback for the variables we are using
$cms_username = (!isset($cms_username)) ? '' : $cms_username; 
?>

<h2>LOGIN</h2>

<form id="cms-login" class="cms-form clearfix" method="post" action="<?=myUrl('cms/login')?>">
	<label>Username</label><input type="text" name="cms_username" value="<?=$cms_username?>" />
	<label>Password</label><input type="password" name="cms_password" value="" />
	<input type="submit" name="submit" class="button" value="Login" />
</form>