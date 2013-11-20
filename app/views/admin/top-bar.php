<div id="admin-topbar">
	<h3><a href="http://kisscms.com/" title="Made with love...">KISSCMS</a></h3>
	<ul>
<?php if( (isset( $id ) && !isset( $status )) || (isset( $id ) && isset( $status ) && $status !== "edit") ){ ?>
		<li><a href="<?=url("admin/edit/$id")?>">Edit page</a></li>
<?php } elseif( isset( $path ) && isset( $status ) && $status == "new" ) { ?>
		<li><a href="<?=url("admin/create/".$path)?>">Create page</a></li>
<?php } elseif( isset( $path ) && isset( $status ) && $status == "edit") { ?>
		<li><a href="<?=url( $path )?>">View page</a></li>
<?php } ?>
<?php 	if( isset( $id ) && $id != "1"){ ?>
		<li><a href="<?=url("admin/delete/$id")?>" onclick="return confirm('<?=$GLOBALS['language']['delete_confirm']?>')">Delete page</a></li>
<?php } ?>
<?php 	if( !isset( $status ) || (isset( $status ) && $status != "config") ){ ?>
		<li><a href="<?=url("admin/config")?>">Configuration</a></li>
<?php } ?>
		<li><a href="<?=url("admin/logout")?>">Logout</a></li>
	</ul>
</div>