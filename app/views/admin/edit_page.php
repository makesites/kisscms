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

	<label>Tags</label>
    
	<input type="text" name="tags" id="tags" value="<?=$tags?>" />

	<input type="submit" value="<?=$GLOBALS['language'][$status.'_button']?>" id="edit-button" class="button" />
</form>

<script src="http://code.jquery.com/jquery-1.6.1.min.js" type="text/javascript" charset="utf-8"></script>
<script src="<?=ASSETS_PATH?>js/admin/jquery-ui-1.8.core-and-interactions.min.js" type="text/javascript" charset="utf-8"></script>
<script src="<?=ASSETS_PATH?>js/admin/jquery-ui-1.8.autocomplete.min.js" type="text/javascript" charset="utf-8"></script>
<script src="<?=ASSETS_PATH?>js/admin/tag-it.js" type="text/javascript" charset="utf-8"></script>

<script>
	$(document).ready(function(){
		$("#tags").tagit({
			availableTags: []
		});
	});
</script>
