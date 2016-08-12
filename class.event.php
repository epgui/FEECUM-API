<?php

class Event
{
  protected $id;
  protected $t_start;
  protected $t_end;
  protected $cat_id;
  protected $category;
  protected $summary;
  protected $description;
  protected $warnings = [];
  protected $errors = [];

  function __construct($event_id)
  {
    $this->id = $event_id;
    $this->populate_event_data();
  }

  public function get_id()
  {
    return $this->id;
  }

  public function get_unix_timestamp_start()
  {
    return $this->t_start;
  }

  public function get_unix_timestamp_end()
  {
    return $this->t_end;
  }

  public function get_category()
  {
    return $this->category;
  }

  public function get_summary()
  {
    return $this->summary;
  }

  public function get_description()
  {
    return $this->description;
  }

  public function get_warnings()
  {
    return $this->warnings;
  }

  public function set_warning($d)
  {
    array_push($this->warnings, $d);
  }

  public function get_errors()
  {
    return $this->errors;
  }

  public function set_error($d)
  {
    array_push($this->errors, $d);
  }

  private function populate_event_data()
  {
    // Build query
    $q  = "SELECT * FROM x8g2f_jevents_vevdetail";
    $q .= " WHERE evdet_id = " . $this->id;
    $q .= " LIMIT 1"; // Assume record id uniqueness

    // Find the database connection
    $db = Database::getInstance();

    // Check connection status
    if ($db->get_status() == "Disconnected")
    {
      if ($db->get_error_message())
      {
        $d = "Database connection error: " . $db->get_error_message();
        $this->set_error($d);
      }
      else
      {
        $d = "Unspecified database connection error.";
        $this->set_error($d);
      }
    }
    else
    {
      // Make sure query is successful
      if (!$result = $db->execute_query($q))
      {
        if ($db->get_error_message())
        {
          $d = "MySQL query error: " . $db->get_error_message();
          $this->set_error($d);
        }
        else
        {
          $d = "MySQL query returned an unspecified error.";
          $this->set_error($d);
        }
      }
      else
      {
        if ($result->num_rows <= 0)
        {
          $d = "Event not found.";
          $this->set_error($d);
        }
        else
        {
          // Grab single record
          $event_record = $result->fetch_assoc();

          // Populate relevant properties
          $this->t_start     = $event_record["dtstart"];
          $this->t_end       = $event_record["dtend"];
          $this->description = $event_record["description"];
          $this->summary     = $event_record["summary"];

          // Populate category data
          $this->populate_event_category_id();
          $this->populate_event_category();

          $result->free();
        }
      }
    }
  }

  private function populate_event_category_id()
  {
    // Build query
    $q  = "SELECT * FROM x8g2f_jevents_vevent";
    $q .= " WHERE detail_id = " . $this->id;
    $q .= " LIMIT 1"; // Assume record id uniqueness

    // Find the database connection
    $db = Database::getInstance();

    // Check connection status
    if ($db->get_status() == "Disconnected")
    {
      if ($db->get_error_message())
      {
        $d = "Database connection error: " . $db->get_error_message();
        $this->set_error($d);
      }
      else
      {
        $d = "Unspecified database connection error.";
        $this->set_error($d);
      }
    }
    else
    {
      // Make sure query is successful
      if (!$result = $db->execute_query($q))
      {
        if ($db->get_error_message())
        {
          $d = "MySQL query error: " . $db->get_error_message();
          $this->set_error($d);
        }
        else
        {
          $d = "MySQL query returned an unspecified error.";
          $this->set_error($d);
        }
      }
      else
      {
        if ($result->num_rows <= 0)
        {
          $d = "Event category could not be found.";
          $this->set_warning($d);
        }
        else
        {
          // Populate the category id
          $event_record = $result->fetch_assoc();
          $this->cat_id = $event_record["catid"];

          $result->free();
        }
      }
    }
  }

  private function populate_event_category()
  {
    // Build query
    $q  = "SELECT * FROM x8g2f_categories";
    $q .= " WHERE id = " . $this->cat_id;
    $q .= " LIMIT 1"; // Assume record id uniqueness

    // Find the database connection
    $db = Database::getInstance();

    // Check connection status
    if ($db->get_status() == "Disconnected")
    {
      if ($db->get_error_message())
      {
        $d = "Database connection error: " . $db->get_error_message();
        $this->set_error($d);
      }
      else
      {
        $d = "Unspecified database connection error.";
        $this->set_error($d);
      }
    }
    else
    {
      // Make sure query is successful
      if (!$result = $db->execute_query($q))
      {
        if ($db->get_error_message())
        {
          $d = "MySQL query error: " . $db->get_error_message();
          $this->set_error($d);
        }
        else
        {
          $d = "MySQL query returned an unspecified error.";
          $this->set_error($d);
        }
      }
      else
      {
        if ($result->num_rows <= 0)
        {
          $d = "Category could not be found.";
          $this->set_warning($d);
        }
        else
        {
          // Populate the category id
          $category_record = $result->fetch_assoc();
          $this->category = $category_record["title"];

          $result->free();
        }
      }
    }
  }
}

?>
