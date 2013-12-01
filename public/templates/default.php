<!doctype html>
<html>
<head>
	<title><? Meta::title() ?></title>
	<? Template::head(); ?>

	<link href='http://fonts.googleapis.com/css?family=Orbitron:400,700' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="<?=url("/assets/css/main.css")?>" type="text/css" media="screen" />

</head>

<body class="<?=$_page['view']?>">

<div class="page">
	<header>
		<div id="nav" class="pink-gd r5"><? Menu::ul(); ?></div>

		<h1><a href="<?=url()?>"><?=$config['main']['site_name']?></a></h1>

	</header>

	<div id="main" role="main">

		<? Template::body(); ?>

	</div>
	<aside class="sidebar">

		<? Search::view()?>

		<? LatestUpdates::ul("class: 'r10 gray-tr'")?>

		<? Archive::ul("class: 'r10 gray-tr'")?>

		<? Tags::cloud('id: tagcloud, weight: 1')?>

	</aside>
	<footer>
		<? Copyright::view() ?>
	</footer>
</div>

<? Template::foot(); ?>

</body>
</html>
