<?php
require('kissmvc_core.php');

//===============================================================
// Model/ORM
//===============================================================
class Model extends KISS_Model  {

  //Example of adding your own method to the core class
  function gethtmlsafe($key) {
    return htmlspecialchars($this->get($key));
  }

}

//===============================================================
// Controller
//===============================================================
class Controller extends KISS_Controller {

  //Example of overriding a core class method with your own
  function request_not_found() {
    die(View::do_fetch(VIEW_PATH.'errors/404.php'));
  }

}

//===============================================================
// View
//===============================================================
class View extends KISS_View {

  //Example of overriding a constructor/method, add some code then pass control back to parent
  function __construct($file='',$vars='') {
    $file = VIEW_PATH.$file;
    return parent::__construct($file,$vars);
  }

}

?>