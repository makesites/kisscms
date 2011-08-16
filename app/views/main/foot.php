
<script type="text/javascript">
//Load jQuery before any other scripts, since jQuery plugins normally
//assume jQuery is already loaded in the page.
require(['<?=myCDN()?>js/libs/jquery-1.5.1.min.js'], function() {
		
		// open external links in new window 
		$('a[rel*=external]').click( function() { 
			window.open(this.href, '_blank'); return false; 
		});

});

</script>

<!--[if lt IE 7 ]>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/chrome-frame/1/CFInstall.min.js"></script>
<script src="<?=myCDN()?>js/libs/dd_belatedpng.js"></script>
<script>DD_belatedPNG.fix("img, .png_bg");</script>
<![endif]-->
