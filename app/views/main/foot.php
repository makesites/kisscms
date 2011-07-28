<script type="text/javascript" src="js/libs/require.js"></script>

<script type="text/javascript">
//Configure RequireJS
require({
    //Load jQuery before any other scripts, since jQuery plugins normally
    //assume jQuery is already loaded in the page.
    priority: ['jquery-1.5.1.min']
});

//Load scripts.
require(['jquery-1.5.1.min', 'js/plugins', 'js/script.js'], function($) {
    $(function() {
       // do something...
    });
});
</script>

<!--[if lt IE 7 ]>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/chrome-frame/1/CFInstall.min.js"></script>
<script src="js/libs/dd_belatedpng.js"></script>
<script>DD_belatedPNG.fix("img, .png_bg");</script>
<![endif]-->
