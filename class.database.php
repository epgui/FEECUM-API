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

  public function connect_to_database()
  {
    if ($this->status == "Disconnected")
    {
      // Open a connection to MySQL
      $this->db_connection = new mysqli($this->mysql_host, $this->mysql_user, $this->mysql_password, $this->mysql_database);

      // Change character set to UTF-8
      if (!$this->db_connection->set_charset("utf8"))
      {
        $this->error_message = "Error loading character set utf8: " . $this->db_connection->error;
        return $this->disconnect();
      }
    }
    return $this->check_database_connection();
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
