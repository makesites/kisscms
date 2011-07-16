<style>
body{
	padding-top: 24px;
}
</style>
<div id="cms-topbar">
	<h3><a href="http://kisscms.com/" title="Visit the website for helpful tips and plugins...">KISSCMS</a></h3>
	<ul>
<?php if( isset( $status ) && $status == "new"){ ?>
		<!-- <li><a href="<?=getURL("admin/create", true)?>">Create page</a></li> -->
<?php } else { ?>
		<li><a href="<?=getURL("admin/edit/$id", true)?>">Edit page</a></li>
<?php 	if( isset( $id ) && $id != "1"){ ?>
		<li><a href="<?=getURL("admin/delete/$id", true)?>" onclick="return confirm('<?=$GLOBALS['language']['delete_confirm']?>')">Delete page</a></li>
<?php 	} ?>
<?php } ?>
		<li><a href="<?=getURL("admin/config", true)?>">Configuration</a></li>
		<li><a href="<?=getURL("admin/logout", true)?>">Logout</a></li>
	</ul>
</div>
