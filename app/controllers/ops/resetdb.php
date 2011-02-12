<?php
function _resetdb() {
  $dbh=getdbh();
  $dbh->exec('DROP TABLE "users"');
  $dbh->exec('VACUUM');
  $sql = 'CREATE TABLE "users" ("uid" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL , "username" VARCHAR NOT NULL , "password" VARCHAR NOT NULL , "fullname" , "created_dt" DATETIME)';
  $dbh->exec($sql);
  $dbh->exec("INSERT INTO users (username,password,fullname,created_dt) VALUES ('admin','pass','Tester','".date('Y-m-d H:i:s')."')");
  for ($i=1;$i < 21; $i++) {
    $username='user'.$i;
    $password='pass'.$i;
    $fullname='User #'.$i;
    $dbh->exec("INSERT INTO users (username,password,fullname,created_dt) VALUES ('$username','$password','$fullname','".date('Y-m-d H:i:s')."')");
  }
  redirect('main','Database Initialized!');
}