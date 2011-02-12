<?php
function myUrl($url='',$fullurl=false) {
  $s=$fullurl ? WEB_DOMAIN : '';
  $s.=WEB_FOLDER.$url;
  return $s;
}

function redirect($url,$alertmsg='') {
  if ($alertmsg)
    addjAlert($alertmsg,$url);
  header('Location: '.myUrl($url));
  exit;
}

function require_login() {
  if (!isset($_SESSION['authuid']))
    redirect('main/login');
}

//session must have started
//$uri indicates which uri will activate the alert (substring check)
function addjAlert($msg,$uri='') {
  if ($msg) {
    $s="alert(\"$msg\");";
    $_SESSION['jAlert'][]=array($uri,$s);
  }
}

function getjAlert() {
  if (!isset($_SESSION['jAlert']) || !$_SESSION['jAlert'])
    return '';
  $pageuri=$_SERVER['REQUEST_URI'];
  $current=null;
  $remainder=null;
  foreach ($_SESSION['jAlert'] as $x) {
    $uri=$x[0];
    if (!$uri || strpos($pageuri,$uri)!==false)
      $current[]=$x[1];
    else
      $remainder[]=$x;
  }
  if ($current) {
    if ($remainder)
      $_SESSION['jAlert']=$remainder;
    else
      unset($_SESSION['jAlert']);
    return '<script type="text/javascript">'."\n".implode("\n",$current)."\n</script>\n";
  }
  return '';
}