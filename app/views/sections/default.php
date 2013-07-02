<section <?=attr("id", $vars['id'])?> <?=attr("class", $vars['class'])?>>
<? if(!empty($vars['h3'])){ ?>
<h3 <?=attr("id", $vars['h3-id'])?> <?=attr("class", $vars['h3-class'])?>><?=$vars['h3']?></h3>
<? } ?>
<ul <?=attr("id", $vars['ul-id'])?> <?=attr("class", $vars['ul-class'])?>>
<? if(!empty($items)){ ?>
<? foreach($items as $item){ ?>
	<? $selected = ( array_key_exists('selected', $item) ) ? $item['selected'] : NULL; ?>
	<li <?=attr("class", $selected)?>><a href="<?=$item['url']?>"><?=$item['title']?></a></li>
<? }
} ?>
</ul>
</section>