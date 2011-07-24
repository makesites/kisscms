<h3>Main Menu</h3>
<ul>
<? foreach($pages as $page){ ?>
	<li><a href="<?=$page['url']?>"><?=$page['title']?></a></li>
<? } ?>
</ul>
