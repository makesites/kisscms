<div class="post">
	<div class="posttop">
		<a href="<? url( $path ) ?>" rel="bookmark" title="Permanent Link: <?=$title?>">
			<label>
				<strong class="date"><?=date("d", strtotime($date))?></strong> <strong class="month"><?=date("M", strtotime($date))?></strong>
				<!--strong class="comments">0</strong-->
			</label>
		</a>
	</div>

	<!-- end of .posttop -->
	<div class="postbody">
		<h2><?=$title?></h2>
		<p class="postinfo">
			Posted at <?=date("G:i", strtotime($date))?>
		</p>
		<div><?=$content?></div>
	</div>
	<!-- end of .postfoot -->
	<div class="postmeta">
		<? Tags::inline("class: right, delimiter: ',', h3: 'Tags:'", $tags)?>
	</div>
	<!-- end of .postmeta -->
</div>
<!-- end of .post -->

