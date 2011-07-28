<section id="<?=$vars['id']?>" class="<?=$vars['class']?>">
<? if(!empty($vars['h3'])){ ?>
<h3 id="<?=$vars['h3-id']?>" class="<?=$vars['h3-class']?>"><?=$vars['h3']?></h3>
<? } ?>
<div id="tag_holder">
<? if(!empty($items)){ ?>
<? foreach($items as $item){ ?>
	<a href="<?=$item['url']?>" class="tag"<? if($vars['weight']) echo ' style="font-size: '. (100+($item['weight']*10)). '%"' ?>><?=$item['title']?></a>
<? } 
} ?>
</div>
</section>