<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

<meta name="title" content="<? Meta::title(); ?>" />
<meta name="description" content="<? Meta::description(); ?>" />
<meta name="author" content="<?=$config['main']['site_author']?>" />
<meta property="og:title" content="<? Meta::title(); ?>" />
<meta property="og:type" content="website" />
<meta property="og:url" content="<? Meta::url(); ?>" />
<meta property="og:image" content="/apple-touch-icon-114x114-precomposed.png" />
<meta property="og:site_name" content="<?=$config['main']['site_name']?>" />
<meta property="og:description" content="<? Meta::description(); ?>" />

<base href="<?=str_replace('/index.php','',myUrl('',true))?>" />

<link rel="shortcut icon" href="/favicon.ico">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="stylesheet" href="<?=myCDN()?>/css/style.css">
<link type="text/plain" rel="author" href="<?=myUrl()?>/humans.txt" />

<script type="text/javascript" src="<?=myCDN()?>/js/libs/require.js"></script>
<script type="text/javascript" src="<?=myCDN()?>/js/libs/modernizr-1.7.min.js"></script>
