<? if(isset($h3)){ ?>
<h3 id="<?=$h3['id']?>" class="<?=$h3['class']?>">Main Menu</h3>
<? } ?>
<ul>
<? foreach($pages as $page){ ?>
	<li><a href="<?=$page['url']?>"><?=$page['title']?></a></li>
<? } ?>
</ul>
