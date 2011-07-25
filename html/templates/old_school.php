<!doctype html>
<!--[if lt IE 7 ]> <html class="no-js ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]>    <html class="no-js ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]>    <html class="no-js ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<title><?=Meta::display('title')?></title>
	<? Template::display( $head ); ?>
    <link rel="stylesheet" href="<?=myUrl()?>/assets/css/old_school.css" type="text/css" media="screen" />
</head>

<body>


<? Section::display("menu"); ?>


<div id="article">
	<? Template::display( $body ); ?>
</div>


<? Template::display( $foot ); ?>

</body>
</html>
