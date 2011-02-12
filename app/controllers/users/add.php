<?php
function _add() {
  require_login();    
  $user=new User();
  $fdata['form_heading']='Add User';
  $fdata['user']=$user;
  $form = View::do_fetch(VIEW_PATH.'users/edit.php',$fdata);
  $data['head'][]=View::do_fetch(VIEW_PATH.'users/edit_js.php');
  $data['body'][]='<h2>Add New User</h2>';
  $data['body'][]= $form;
  View::do_dump(VIEW_PATH.'layouts/mainlayout.php',$data);
}