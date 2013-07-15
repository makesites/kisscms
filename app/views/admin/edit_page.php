<?php
// create a fallback for the variables we are using
$title = (!isset($title)) ? '' : $title;
$content = (!isset($content)) ? '' : $content;
$action = ( $status == "create" ) ? url("admin/update", true) : url("admin/update/id/$id", true);
?>

<h2><?=$GLOBALS['language'][$status.'_title']?></h2>

<p><?=$GLOBALS['language'][$status.'_description']?></p>

<form class="cms-form clearfix" method="post" action="<?=$action?>">
	<label for="title">Title</label>
	<input type="text" id="title" name="title" value="<?=$title?>" />

	<label for="content">Content</label>
	<textarea id="content" name="content"><?=$content?></textarea>

	<label for="tags">Tags</label>
	<p>(Separate tags with commas)</p>
	<input type="text" name="tags" id="tags" value="<?=$tags?>" />

	<label for="template">Template</label>
	<?=Template::doList($template);?>

	<label for="content">Page URL</label>
	<p><?=url("$path")?></p>
	<input type="hidden" name="path" value="<?=$path?>" />


	<input type="submit" value="<?=$GLOBALS['language'][$status.'_button']?>" id="edit-button" class="button" />
</form>

<script type="text/javascript" src="<?=url('/js/libs/jquery-ui-1.8.core-and-interactions.min.js')?>" data-type="require" data-path="jquery-ui-core" data-deps="jquery"></script>
<script type="text/javascript" src="<?=url('/js/libs/jquery-ui-1.8.autocomplete.min.js')?>" data-type="require" data-path="jquery-ui-autocomplete" data-deps="jquery-ui-core"></script>
<script type="text/javascript" src="<?=url('/js/plugins/tag-it.js')?>" data-type="require" data-path="tag-it" data-deps="jquery-ui-autocomplete"></script>
<script type="text/javascript" data-type="require" data-deps="tag-it">

	$(function(){
		$(".cms-form #tags").tagit({
			availableTags: []
		});
	});

</script>
