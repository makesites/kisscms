<?php 
// create a fallback for the variables we are using
$title = (!isset($title)) ? '' : $title; 
$content = (!isset($content)) ? '' : $content; 
$action = ( $status == "create" ) ? myUrl("admin/update") : myUrl("admin/update/$id"); 
?>

<h2><?=$GLOBALS['language'][$status.'_title']?></h2>

<p><?=$GLOBALS['language'][$status.'_description']?></p>

<form class="cms-form clearfix" method="post" action="<?=$action?>">
	<input type="hidden" name="path" value="<?=$path?>" />

	<label>Title</label>
	<input type="text" name="title" value="<?=$title?>" />

	<label>Content</label>
	<textarea name="content"><?=$content?></textarea>

	<input type="submit" value="<?=$GLOBALS['language'][$status.'_button']?>" id="edit-button" class="button" />
</form>