<?php 
// create a fallback for the variables we are using
$admin_username = (!isset($admin_username)) ? '' : $admin_username; 
?>

<h2>LOGIN</h2>

<form id="cms-login" class="cms-form clearfix" method="post" action="<?=myUrl('admin/login')?>">
	<label>Username</label><input type="text" name="admin_username" value="<?=$admin_username?>" />
	<label>Password</label><input type="password" name="admin_password" value="" />
	<input type="submit" name="submit" class="button" value="Login" />
</form>