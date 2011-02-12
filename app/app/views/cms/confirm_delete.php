<h2>Are you sure?</h2>

<p>You are about to delete the page: <?=$path?></>

<p>Do you want to proceed?</p>

<form action="<?=myUrl("cms/create")?>">
<input type="hidden" name="path" value="<?=$path?>">
<input type="submit" value="Delete" />
</form>