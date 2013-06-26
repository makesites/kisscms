<canvas <?=attr("id", $vars['id'])?> <?=attr("class", $vars['class'])?>>
<div id="tag-holder" class="hide">
<? if(!empty($items)){
	foreach($items as $item){
	// excluding empty tags and tags related with organizing content (better logic here...)
	if( $item['title'] != "" && strpos( $item['title'], "menu-" ) == false && $item['title'] != "category" ){ ?>
	<a href="<?=$item['url']?>" class="tag"<? if($vars['weight']) echo ' style="font-size: '. (100+($item['weight']*10)). '%"' ?>><?=$item['title']?></a>
<?  } ?>
<? }
} ?>
</div>
</canvas>
<script type="text/javascript" src="<?=url('/js/libs/jquery-1.7.2.min.js')?>"></script>
<script type="text/javascript" src="<?=url('/js/plugins/jquery.tagcanvas.min.js')?>"></script>
<script type="text/javascript">
	$(function(){
		$("#<?=$vars['id']?>").tagcanvas({
				 depth : 0.75
		}, "tag-holder");
	});
</script>