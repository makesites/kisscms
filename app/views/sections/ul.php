<section id="<?=$vars['id']?>" class="<?=$vars['class']?>">
<? if(!empty($vars['h3'])){ ?>
<h3 id="<?=$vars['h3-id']?>" class="<?=$vars['h3-class']?>"><?=$vars['h3']?></h3>
<? } ?>
<ul id="<?=$vars['ul-id']?>" class="<?=$vars['ul-class']?>">
<? if( isset($items) && !empty($items) ){ ?>
<? foreach($items as $item){ ?>
	<li><a href="<?=$item['url']?>"><?=$item['title']?></a></li>
<? } 
} ?>
</ul>
</section>