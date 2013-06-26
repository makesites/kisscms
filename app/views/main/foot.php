
<!--[if lt IE 7 ]>
<script src="//ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script>
<script>window.attachEvent('onload',function(){CFInstall.check({mode:'overlay'})})</script>
<![endif]-->

<script type="text/javascript" data-type="client">
	Object.extend(KISSCMS, <?=json_encode_escaped( $GLOBALS['client'] )?>);
	require.config( KISSCMS.require );
</script>

<script id="require-main" type="text/javascript" data-main="<?=url("/client")?>" src="<?=url("/js/libs/require.js")?>"  defer="defer"></script>
