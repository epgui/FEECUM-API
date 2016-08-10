<?php

class Database extends Dbconfig
{
  private static $instance; // Database object is a singleton

  private $status = "Disconnected";
  private $error_message;
  private $db_connection;

  public static function getInstance()
  {
    if (null === static::$instance)
    {
      static::$instance = new static();
    }

    return static::$instance;
  }

  protected function __construct()
  {
  }

  private function __clone()
  {
  }

  private function __wakeup()
  {
  }

  public function get_status()
  {
    return $this->status;
  }

  public function get_error_message()
  {
    return $this->error_message;
  }

  public function connect_to_database($mysql_host, $mysql_user, $mysql_password, $mysql_database)
  {
    if ($this->status == "Disconnected")
    {
      $this->db_host     = $db_host;
      $this->db_user     = $db_user;
      $this->db_password = $db_password;
      $this->db_database = $db_database;

      // Open a connection to MySQL
      $this->db_connection = new mysqli($this->db_host, $this->db_user, $this->db_password, $this->db_database);
      return $this->check_database_connection();
    }
    else
    {
      return $this->check_database_connection();
    }
  }

  public function check_database_connection()
  {
    // Check connection
    if ($this->db_connection->connect_errno > 0)
    {
      $this->status = "Disconnected";
      $this->error_message = $this->db_connection->connect_error;
      return $this->disconnect();
    }
    else
    {
      $this->status = "Connected";
      $this->error_message = null;
      return $this->db_connection;
    }
  }

  public function execute_query($q)
  {
    if (!$result = $this->db_connection->query($q))
    {
      $this->status = "Disconnected";
      $this->error_message = $this->db_connection->error;
      return $this->disconnect();
    }
    else
    {
      return $result;
    }
  }

  public function disconnect()
  {
    $this->db_connection->close();
    return false;
  }
}

?>
