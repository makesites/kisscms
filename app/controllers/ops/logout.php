<?php
function _logout() {
  unset($_SESSION['authuid']);
  redirect('main','You have logged out!');
}