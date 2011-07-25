<!doctype html>
<!--[if lt IE 7 ]> <html class="no-js ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]>    <html class="no-js ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]>    <html class="no-js ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<title><?=Meta::display('title')?></title>
	<? Template::display( $head ); ?>

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
	
<form action="http://www.google.com/search" class="print-hide">
	<input maxlength="2048" name="q" size="20" title="Google Search" value="">
	<input value="Search" type="submit">
</form>

	</div>
	<div class="clear"></div>
</div>

				</div>
				<div id="main-menu" class="clearfix rMenu-center">
					<hr class="hide">


<? Section::display("menu", "rmenu"); ?>

				</div>
				<div id="binding">
					<div id="page">
						<hr class="hide">
						<div class="panel">

<? Template::display( $body ); ?>

						</div>
						<div id="left-column">
							<div class="panel">

<h3>Tricks To The Layout</h3>
<p>
	Things you've seen before simplified and reworked for better efficiency. A narrow right-hand
	toolbar is used to place various bits that users may find useful. For example the dynamic font-size
	buttons which will resize the text within the layout. The colored square buttons provide
	a quick means for users to customize the layout to their liking. But it need not rely just be
	colors! You might include stylesheets that change the font type or width of the columns, perhaps
	even hide the right column entirely! The style switcher provides you with an endless array of 
	options.
</p>

							</div>
							<div class="panel">

<div class="two-columns tc-border">
	<div class="tc-left-column">
		<div class="tc-panel">

	<h3>No Column Backgrounds?</h3>
	<p>
		A highlight of past layouts found here, but not in this one! There is no background color to 
		give a visual definition of columns through the full height of the page. The reason for this: 
		keep the layout simple. None of this "inside-container" "outside-container" nonsense. It just
		clutters things up and creates more places for browsers to break the layout. It should
		also prove easier to manage for the novice web developer. (Although the CSS is sure to give
		you headaches.)
	</p>

		</div>
	</div>
	<div class="tc-right-column">
		<div class="tc-panel">
	
<h3>Panels</h3>
<p>
	You probably have already noticed that I've placed each section of this column 
	into its own block. In fact the right column can be done up the same way. It's
	a bit blogish in style, but that does seem to be the "in-thing" these days. 
	However you need not go to such lengths. One block in each column will surely 
	suffice. It's yet another option you have in managing the design of the layout.
</p>

		</div>
	</div>
	<div class="clear"></div>
</div>

							</div>

						</div>
						<div id="right-column">

							<div class="panel">

<h3>Compatibility</h3>
<p>
	Confirmed to work with Win/IE 5.5 and later (should work in 5.0, but not confirmed),
	Firefox 2, Safari 3, Opera 9, iCab 3.02 and later, Mac/IE 5, Netscape 6 and later
</p>
<p>
	Old browsers (IE version 4 or earlier, Netscape 4 or earlier) should only see
	a text-based page which, while not the prettiest option, is still entirely
	usable.
</p>

							</div>
							<div class="panel">

<h3>Download</h3>
<p>
	Like other layouts, there is <a href="http://webhost.bridgew.edu/etribou/layouts/download/comic.zip">a 
	ZIP available for download here</a> will give you everything 
	you need to start using this layout. 
</p>
<p>
	If you're new to CSS I suggest opening up the stylesheets
	and playing around with various rules and values to get a 
	feel for how the layout works and what bits of CSS do what. 
	Figuring things out in this manner will help both learn CSS
	and become proficient in solving compatibility issues with
	this and future layouts you develop yourself.
</p>

							</div>
						</div>
						<div class="clear"></div>
					</div>
					<div class="clear"></div>
				</div>
				<div id="footer">
					<hr class="hide">

<p>
	All HTML/CSS is released into the public domain. 
</p>

				</div>
			</div>
		</div>

<? Template::display( $foot ); ?>

</body>
</html>
