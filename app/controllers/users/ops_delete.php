<?php
function _ops_delete($uid=0) {
  require_login();    
  $msg='';
  $uid=max(0,intval($uid));
  $user=new User($uid);
  if (!$user->exists())
    $msg='User not found!';
  else {
    if ($user->delete())
      $msg='User deleted!';
    else
      $msg='User delete failed!';
  }   
  redirect('users/manage',$msg);
}