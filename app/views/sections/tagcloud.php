<canvas id="<?=$vars['id']?>" class="<?=$vars['class']?>">
<div id="tag-holder" class="hide">
<? if(!empty($items)){
	foreach($items as $item){
	// excluding empty tags and tags related with organizing content (better logic here...)
	if( $item['title'] != "" && strpos( $item['title'], "menu-" ) == false ){ ?>
	<a href="<?=$item['url']?>" class="tag"<? if($vars['weight']) echo ' style="font-size: '. (100+($item['weight']*10)). '%"' ?>><?=$item['title']?></a>
<?  } ?>
<? } 
} ?>
</div>
</canvas>
<script type="text/javascript">
require(['<?=myUrl()?>/js/libs/jquery-1.5.1.min.js', '<?=myUrl()?>/js/plugins/jquery.tagcanvas.min.js'], function() {
		$("#<?=$vars['id']?>").tagcanvas({
			 depth : 0.75
		}, "tag-holder");
});
</script>