<?php
function _ops_update() {
  require_login();    
  $msg='';
  $uid=max(0,intval($_POST['uid']));
  $user=new User();
  if ($uid) {
    $user->retrieve($uid);
    $user->merge($_POST);
    if (!$user->exists())
      $msg='User not found!';
    else
      if ($user->update())
        $msg='User updated!';
      else
        $msg='User update failed!';
  }
  else {
    $user->merge($_POST);
    if ($user->create())
      $msg='User inserted!';
    else
      $msg='User insert failed!';
  }
  redirect('users/manage',$msg);
}