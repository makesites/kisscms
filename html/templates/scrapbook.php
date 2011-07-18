<!doctype html>
<!--[if lt IE 7 ]> <html class="no-js ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]>    <html class="no-js ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]>    <html class="no-js ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<title><?=$GLOBALS['sitename']?></title>
	
	<? Template::display( $head ); ?>

</head>

<body>

  <div id="container">
    <header>
    <h1><a href="/"><?=$GLOBALS['sitename']?></a></h1>
<div id="nav">

<? mainMenu(); ?>

  </div>
    </header>
    <div id="main" role="main">


<? Template::display( $body ); ?>


    </div>
    <footer>

    </footer>
  </div> <!-- eo #container -->

<? Template::display( $foot ); ?>

</body>
</html>
