<?php
$authuid=isset($_SESSION['kisscms_admin']) ? $_SESSION['kisscms_admin'] : 0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<base href="<?=str_replace('/index.php','',myUrl('',true))?>" />
<title><?=$GLOBALS['config']['sitename']?></title>
<style type="text/css">
  @import "assets/css/reset.css";
  @import "assets/css/text.css";
  @import "assets/css/2col.css";
</style>

<?php if(isset($cms_styles)){ ?>
	<link rel="stylesheet" type="text/css" media="screen"  href="<?=ASSETS_PATH?>css/cms.css" />
<?php } ?>

<?php
if (isset($head) && is_array($head))
  foreach ($head as $blockhtml)
    echo "$blockhtml\n";
?>
</head>
<body>

<?php if(isset($cms_topbar)){
    echo "$cms_topbar\n";
} ?>

<div id="wrap">
  <div id="header"><h1><a href="http://www.makesites.cc/projects/kisscms" title="The Simple PHP MVC Framework">KISSCMS</a> - Simple CMS Based On <a href="http://kissmvc.com" title="The Simple PHP MVC Framework">KISSMVC</a> </h1></div>
  <div id="nav">
    <ul>
      <li><a href="<?=myUrl('')?>">Main</a></li>
<?php
  if ($authuid)
    echo '<li><a href="'.myUrl('cms/logout').'">Logout</a></li>'."\n";
  else
    echo '<li><a href="'.myUrl('cms/login').'">Login</a></li>'."\n";  
?>
    </ul>
  </div>
  <div id="main">
<?php
if (isset($body) && is_array($body))
  foreach ($body as $blockhtml)
    echo "$blockhtml\n";
?>
  </div>
  <div id="sidebar">

<? mainMenu(); ?>

<?php
if (isset($leftnav) && is_array($leftnav))
  foreach ($leftnav as $blockhtml)
    echo "$blockhtml\n";
?>
  </div>
  <div id="footer">
    <p>Footer</p>
  </div>
</div>
</body>
</html>
