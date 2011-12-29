<form action="http://www.google.com/search" role="search" method="get" id="searchform" onSubmit="Gsitesearch(this)">
	<input name="q" type="hidden" />
	<input name="qfront" class="field" type="text" />
	<input type="submit" class="button" value="search" />
</form>

<script type="text/javascript">

var domainroot="<?=url()?>"

function Gsitesearch(curobj){
curobj.q.value="site:"+domainroot+" "+curobj.qfront.value
}

</script>
