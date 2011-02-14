<?php
class CMS extends Model {

  function __construct($id='') {
    parent::__construct('id','pages'); //primary key = id; tablename = pages
    $this->rs['id'] = '';
    $this->rs['title'] = '';
    $this->rs['content'] = '';
    $this->rs['path'] = '';
    $this->rs['date']= '';
    if ($id)
      $this->retrieve($id);
  }

  function create() {
    $this->rs['date']=date('Y-m-d H:i:s');
    return parent::create();
  }

  function update() {
    $this->rs['date']=date('Y-m-d H:i:s');
    return parent::update();
  }

  function get_page_from_path( $uri ) {
    $dbh=getdbh();
    $sql = 'SELECT * FROM "pages" WHERE "path"="'. $uri . '" LIMIT 1';
    $results = $dbh->prepare($sql);
    //$results->bindValue(1,$username);
    $results->execute();
    $page = $results->fetch(PDO::FETCH_ASSOC);
    if (!$page)
      return false;
    foreach ($page as $k => $v)
      $this->set($k,$v);
    return true;
  }

}
?>