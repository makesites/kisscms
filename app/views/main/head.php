<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

<meta name="title" content="<? Meta::title(); ?>">
<meta name="description" content="<? Meta::description(); ?>">
<meta name="author" content="<?=$config['main']['site_author']?>">
<meta name="generator" content="KISSCMS">
<meta property="og:title" content="<? Meta::title(); ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<? Meta::url(); ?>">
<meta property="og:image" content="<?=url('/apple-touch-icon-114x114-precomposed.png')?>">
<meta property="og:site_name" content="<?=$config['main']['site_name']?>">
<meta property="og:description" content="<? Meta::description(); ?>">
<meta property="og:locale" content="en_US">

<base href="<?=url('/')?>">

<link rel="shortcut icon" href="<?=url('/favicon.ico')?>">
<link rel="apple-touch-icon" href="<?=url('/apple-touch-icon.png')?>">
<link rel="apple-touch-icon-precomposed" href="<?=url('/apple-touch-icon-precomposed.png')?>">
<link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?=url('/apple-touch-icon-72x72-precomposed.png')?>" />
<link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?=url('/apple-touch-icon-114x114-precomposed.png')?>" />
<link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?=url('/apple-touch-icon-144x144-precomposed.png')?>" />

<link rel="stylesheet" href="<?=url('/css/style.css')?>">
<!-- link rel="stylesheet" href="<?=url('/assets/css/common.css')?>" -->
<link type="text/plain" rel="author" href="<?=url('/humans.txt')?>">

<script type="text/javascript"  data-type="require">
	var KISSCMS = {};

Object.extend = function(destination, source) {
  for (var property in source) {
    if (source[property] && source[property].constructor &&
     source[property].constructor === Object) {
      destination[property] = destination[property] || {};
      arguments.callee(destination[property], source[property]);
    } else {
      destination[property] = source[property];
    }
  }
  return destination;
};

<? if( DEBUG){ ?>

	var DEBUG = true;

	// raw out put of the client side vars
	Object.extend(KISSCMS, <?=json_encode_escaped( $GLOBALS['client'] )?>);

<? } ?>

</script>

<script type="text/javascript" src="<?=url('/js/libs/modernizr-2.0.6.min.js')?>"></script>