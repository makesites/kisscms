<?php
function _login() {
  $username=trim($_POST['username']);
  $password=$_POST['password'];

  $user=new User();
  $user->retrieve_one('username=?',$username);
  if (!$user->exists()) {
    unset($_SESSION['authuid']);
    redirect('main/login/'.$username,'Login Failed!');
  }
  if ($password!=$user->get('password')) {
    unset($_SESSION['authuid']);
    redirect('main/login/'.$username,'Wrong Password!');
  }
  //Login Succeeded
  $_SESSION['authuid']=$user->get('uid');
  redirect('main','Login Successful!');
}