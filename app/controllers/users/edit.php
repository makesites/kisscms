<?php
function _edit($uid=0) {
  require_login();    

  $user=new User();
  $user->retrieve($uid);
  if (!$user->exists())
    $data['body'][]='<p>User Not Found!</p>';
  else {
    $fdata['form_heading']='Edit User';
    $fdata['user']=$user;
    $form = View::do_fetch(VIEW_PATH.'users/edit.php',$fdata);
    $data['head'][]=View::do_fetch(VIEW_PATH.'users/edit_js.php');
    $data['body'][]='<h2>Edit User</h2>';
    $data['body'][]=$form;
  }
  View::do_dump(VIEW_PATH.'layouts/mainlayout.php',$data);
}