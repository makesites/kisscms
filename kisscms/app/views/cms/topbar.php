<style>
body{
	padding: 24px;
}
</style>
<div id="cms-topbar">
	<h3>KISSCMS</h3>
	<ul>
<?php if( isset( $status ) && $status == "new"){ ?>
		<!-- <li><a href="<?=myUrl("cms/create")?>">Create page</a></li> -->
<?php } else { ?>
		<li><a href="<?=myUrl("cms/edit/$id")?>">Edit page</a></li>
<?php 	if( isset( $id ) && $id != "1"){ ?>
		<li><a href="<?=myUrl("cms/delete/$id")?>" onclick="return confirm('<?=$GLOBALS['language']['delete_confirm']?>')">Delete page</a></li>
<?php 	} ?>
<?php } ?>
		<li><a href="<?=myUrl("cms/config")?>">Configuration</a></li>
		<li><a href="<?=myUrl("cms/logout")?>">Logout</a></li>
	</ul>
</div>
