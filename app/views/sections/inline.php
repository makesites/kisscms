<section <?=attr("id", $vars['id'])?> <?=attr("class", $vars['class'])?>>
<? if(!empty($vars['h3'])){ ?>
<strong <?=attr("id", $vars['h3-id'])?> <?=attr("class", $vars['h3-class'])?>><?=$vars['h3']?></strong>
<? } ?>
<? 
foreach($items as $item){ ?>
	<a href="<?=$item['url']?>" rel="tag"><?=$item['title']?></a>
<? if ( $vars['delimiter'] && $item != end($items) ){ echo $vars['delimiter'].' '; } ?>
<? } ?>
</section>