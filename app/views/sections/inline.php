<section id="<?=$vars['id']?>" class="<?=$vars['class']?>">
<? if(!empty($vars['h3'])){ ?>
<strong id="<?=$vars['h3-id']?>" class="<?=$vars['h3-class']?>"><?=$vars['h3']?></strong>
<? } ?>
<? 
foreach($items as $item){ ?>
<a href="<?=$item['url']?>" rel="tag"><?=$item['title']?></a>
<? if ( $vars['delimiter'] && $item != end($items) ){ echo $vars['delimiter'].' '; } ?>
<? } ?>
</section>