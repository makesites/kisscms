<!doctype html>
<!--[if lt IE 7 ]> <html class="no-js ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]>    <html class="no-js ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]>    <html class="no-js ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<title><? Meta::title() ?></title>
	<? Template::head(); ?>

    <link rel="stylesheet" href="<?=myUrl()?>/assets/css/cubic.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="<?=myUrl()?>/css/rMenu.css" type="text/css" media="screen" />

</head>
<body style="font-size: 120%;">

		<div id="sleeve">
			<div id="book">
				<div id="masthead">

<div class="two-columns">
	<div class="tc-left-column">

<h1><?=$config['main']['site_description']?></h1>
<h2><?=$config['main']['site_name']?></h2>

	</div>
	<div class="tc-right-column tc-right-align">
	
		<? Search::display("search")?>    		

	</div>
	<div class="clear"></div>
</div>

				</div>
				<div id="main-menu" class="clearfix rMenu-center">
					<hr class="hide">


<? Menu::ul("ul-class: 'clearfix rMenu-hor rMenu'"); ?>

				</div>
				<div id="binding">
					<div id="page">
						<hr class="hide">
						<div class="panel">
BANNER AD

						</div>
						<div id="left-column">
							<div class="panel">

<? Template::body(); ?>


							</div>
							<div class="panel">

<div class="two-columns tc-border">
	<div class="tc-left-column">
		<div class="tc-panel">

	<h3>Latest</h3>
	<p>

	</p>

		</div>
	</div>
	<div class="tc-right-column">
		<div class="tc-panel">
	
<h3>Popular</h3>
<p>

</p>

		</div>
	</div>
	<div class="clear"></div>
</div>

							</div>

						</div>
						<div id="right-column">

							<div class="panel">

<? Archive::ul(); ?>
                

							</div>
							<div class="panel">

<? Tags::inline($body[0]['tags'])?> 
      		

							</div>
						</div>
						<div class="clear"></div>
					</div>
					<div class="clear"></div>
				</div>
				<div id="footer">
					<hr class="hide">

<p><? Copyright::display("copyright")?></p>

				</div>
			</div>
		</div>

<? Template::foot(); ?>

</body>
</html>
