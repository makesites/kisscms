<?php if(isset($_SESSION['admin'])){
	//include("top-bar.php");
?>
<div id="admin-topbar">
	<h3><a href="http://kisscms.com/" title="Made with love...">KISSCMS</a></h3>
	<ul>
<?php if( isset( $status ) && $status == "new"){ ?>
		<!-- <li><a href="<?=myUrl("admin/create", true)?>">Create page</a></li> -->
<?php } elseif( !isset( $status ) ) { ?>
		<li><a href="<?=myUrl("admin/edit/$id", true)?>">Edit page</a></li>
<?php } else { ?>
<?php 	if( isset( $id ) && $id != "1"){ ?>
		<li><a href="<?=myUrl("admin/delete/$id", true)?>" onclick="return confirm('<?=$GLOBALS['language']['delete_confirm']?>')">Delete page</a></li>
<?php 	} ?>
<?php } ?>
		<li><a href="<?=myUrl("admin/config", true)?>">Configuration</a></li>
		<li><a href="<?=myUrl("admin/logout", true)?>">Logout</a></li>
	</ul>
</div>

<? } ?>