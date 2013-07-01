<hr />
<h2><a href="<?=url($path)?>"><?=$title?></a></h2>

<? $_summary = ""; $_summary = truncate(strip_tags($content), 100, " ", "..."); ?>
<? if( !empty($_summary) ){ ?>
<div>
<?=$_summary ?>
</div>
<? } ?>
