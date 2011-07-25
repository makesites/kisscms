<!doctype html>
<!--[if lt IE 7 ]> <html class="no-js ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]>    <html class="no-js ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]>    <html class="no-js ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<title><? Meta::display('title')?></title>
	<? Template::display( $head ); ?>
    <link rel="stylesheet" href="<?=myUrl()?>/assets/css/eclectic.css" type="text/css" media="screen" />
</head>
<body>
	<div id="top">
		<div class="container_12">
			<h1 id="logo" class="left">
				<a href="/" title=""><?=$config['main']['site_name']?></a>
			</h1>
            
			<? Section::display("menu", '{"ul": {"id":"nav", "class":"right"}}'); ?>
            
		</div>
	</div>
	<!-- end of #top -->
	<div id="main">

		<? Section::display("breadcrumb", "{id:'path'}")?> 

		<!-- end of #path -->
		<div id="content">
			<div class="container_12">
				<div class="grid_8">
					<!-- posts starts here -->

					<? Template::render( $body, 'blog' ); ?>
					
                    <? Section::display("pagination")?> 

				</div>
				<!-- end of #content -->
				<div class="grid_4" id="sidebar">

				<? Section::display("archive"); ?>
                
               
			<div class="textwidget"><br></div>
            
            <? Section::display("search")?> 
      		 
				</div>
				<div class="clear">
				</div>
				<!-- end of #sidebar -->
			</div>
		</div>

	</div>
	<!-- end of #main -->
	<div id="foot">
		<div class="container_12">
			<div class="grid_3 formatted">

			</div>
			<div class="grid_3 formatted">

			</div>
			<div class="grid_3 formatted">

			</div>
			<div class="grid_3 formatted">
				
			</div>
			<div class="clear">
			</div>
			<!-- end of grid row -->
			<div class="grid_9">
				<p><? Section::display("copyright")?></p>
			</div>
			<div class="grid_3">
				<img src="./assets/img/eclectic/logo.foot.png" alt="">
			</div>
			<div class="clear">
			</div>
			<!-- end of grid row -->
		</div>
	</div>
	<!-- end of #foot -->

<? Template::display( $foot ); ?>

</body>
</html>
