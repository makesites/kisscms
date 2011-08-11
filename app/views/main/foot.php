
<script type="text/javascript">
//Configure RequireJS
require({
    //Load jQuery before any other scripts, since jQuery plugins normally
    //assume jQuery is already loaded in the page.
    priority: ['js/libs/jquery-1.5.1.min']
});

// open external links in new window 
$('a[rel*=external]').click( function() { 
	window.open(this.href, '_blank'); return false; 
});

</script>

<!--[if lt IE 7 ]>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/chrome-frame/1/CFInstall.min.js"></script>
<script src="js/libs/dd_belatedpng.js"></script>
<script>DD_belatedPNG.fix("img, .png_bg");</script>
<![endif]-->
