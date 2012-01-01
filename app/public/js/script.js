//Load jQuery before any other scripts, since jQuery plugins normally
//assume jQuery is already loaded in the page.
require(['/js/libs/jquery-1.7.1.min.js'], function() {

	$(document).ready(function(){ 
			
		// open external links in new window 
		$('a[rel*=external]').click( function() { 
			window.open(this.href, '_blank'); return false; 
		});
		
	
	});
	
});
