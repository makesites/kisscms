<section id="<?=$id?>" class="<?=$class?>">
<? if(!empty($h3['html'])){ ?>
<h3 id="<?=$h3['id']?>" class="<?=$h3['class']?>"><?=$h3['html']?></h3>
<? } ?>
<ul id="<?=$ul['id']?>" class="<?=$ul['class']?>">
<? if(!empty($li['html'])){ ?>
<? foreach($li['html'] as $item){ ?>
	<li><a href="<?=$item['url']?>"><?=$item['title']?></a></li>
<? } 
} ?>
</ul>
</section>