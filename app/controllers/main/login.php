<?php
function _login($username='') {
  $data['pagename']='Please Login';
  $data['body'][]='<h2>Please Login to Proceed</h2><br />';
  $data['body'][]=View::do_fetch(VIEW_PATH.'main/login_form.php',array('username' => $username));
  $data['body'][]='<p>You can login with username="admin" and password="pass".<br />If that doesnt work try resetting the user database first.</p>';
  View::do_dump(VIEW_PATH.'layouts/mainlayout.php',$data);
}