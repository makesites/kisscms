<h2>Deletion Confirmation</h2>

<p>You are about to delete the page: <?=$path?></>

<p>Do you want to proceed?</p>

<form action="<?=myUrl("admin/delete")?>">
<input type="hidden" name="path" value="<?=$path?>">
<input type="submit" value="Delete" />
</form>