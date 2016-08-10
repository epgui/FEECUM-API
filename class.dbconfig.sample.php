<?php

class Dbconfig
{
  protected $mysql_host;
  protected $mysql_user;
  protected $mysql_password;
  protected $mysql_database;

  function __construct()
  {
    // Insert credentials here
    $this->mysql_host     = "localhost";
    $this->mysql_user     = "company";
    $this->mysql_password = "password";
    $this->mysql_database = "db_name"
  }
}

?>
