
<script type="text/javascript">
//Load jQuery before any other scripts, since jQuery plugins normally
//assume jQuery is already loaded in the page.
require(['<?=url("/js/libs/jquery-1.6.2.min.js")?>'], function() {
		
		// open external links in new window 
		$('a[rel*=external]').click( function() { 
			window.open(this.href, '_blank'); return false; 
		});

});

</script>

<!--[if lt IE 7 ]>
<script src="//ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script>
<script>window.attachEvent('onload',function(){CFInstall.check({mode:'overlay'})})</script>
<![endif]-->
