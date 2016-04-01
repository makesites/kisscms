<?php if( defined("REQUIRE") ){ ?>
<script type="text/javascript" data-type="client">
	Object.extend(KISSCMS, {"require":{"callback": function(){ if(typeof init != "undefined") init(); } }});
</script>

<script id="require-main" type="text/javascript" data-main="<?=url("/client")?>" src="<?=url("/js/libs/require.js")?>"  defer="defer"></script>
<?php } else { ?>
<script type="text/javascript" src="<?=url("/client.js")?>"></script>
<?php } ?>
