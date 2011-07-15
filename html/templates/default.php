<!doctype html>
<html>
<head>
<meta content="charset=utf-8" />
<base href="<?=str_replace('/index.php','',myUrl('',true))?>" />
<title><?=$GLOBALS['sitename']?></title>

<? head($head, $admin); ?>

</head>
<body>

<?php if(isset($cms_topbar)){ echo "$cms_topbar\n"; } ?>

<div id="wrap">
  <div id="header"><h1><a href="http://www.kisscms.com/" title="The Simple PHP MVC Framework">KISSCMS</a> - Simple CMS Based On <a href="http://kissmvc.com/" title="The Simple PHP MVC Framework">KISSMVC</a> </h1></div>
  <div id="nav">
    <ul>
      <li><a href="<?=WEB_DOMAIN?>">Main</a></li>
    </ul>
  </div>
  <div id="main">
  
<? showContent( $body ); ?>

  </div>
  <div id="sidebar">

<? mainMenu(); ?>

<? showContent( $aside ); ?>

  </div>
  <div id="footer">
    <p>Footer</p>
  </div>
</div>
</body>
</html>
