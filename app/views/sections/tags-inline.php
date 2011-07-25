<? foreach($tags as $tag){ ?>
<a href="<?=myUrl("tag/".$tag)?>" rel="tag"><?=$tag?></a><? if ( $tag != end($tags) ){ ?>, <? } ?>
<? } ?>
