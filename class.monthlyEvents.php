<?php

class MonthlyEvents
{
  protected $year_number;
  protected $month_number;
  protected $unix_start_of_month;
  protected $unix_end_of_month;
  protected $events_ids = [];
  protected $events = [];
  protected $warnings = [];
  protected $errors = [];

  function __construct($requested_y, $requested_m)
  {
    // Clearly define range of time requested
    $this->year_number  = $requested_y;
    $this->month_number = $requested_m;
    $this->compute_unix_timestamps();

    // Populate list of every event id requested
    $this->events_ids = $this->populate_list_of_event_ids();

    // Populate list of every event requested
    $this->events = $this->populate_list_of_events();
  }

  public function get_year_number()
  {
    return $this->year_number;
  }

  public function get_month_number()
  {
    return $this->month_number;
  }

  public function get_events_ids()
  {
    return $this->events_ids;
  }

  public function get_events()
  {
    return $this->events;
  }

  public function get_warnings()
  {
    return $this->warnings;
  }

  private function set_warning($d)
  {
    array_push($this->warnings, $d);
  }

  public function get_errors()
  {
    return $this->errors;
  }

  private function set_error()
  {
    array_push($this->warnings, $d);
  }

  private function compute_unix_timestamps()
  {
    $this->unix_start_of_month = "";
    $this->unix_end_of_month = "";

    return null;
  }

  private function populate_list_of_event_ids()
  {
    // Build MySQL query
    $q  = "SELECT evdet_id FROM x8g2f_jevents_vevdetail";
    $q .= " WHERE dtstart >= " . $this->unix_start_of_month;
    $q .= " AND dtstart <= " . $this->unix_end_of_month;
    $q .= " ORDER BY dtstart asc";

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
        // Find all events for that month and format it into an array
        if ($result->num_rows <= 0)
        {
          $d = "No events were found for this month.";
          $this->set_warning($d);
        }
        else
        {
          while($row = $result->fetch_assoc())
          {
            array_push($this->events_ids, $row["evdet_id"]);
          }
          $result->free();
        }
      }
    }
  }

  private function populate_list_of_events()
  {
    if ($this->events_ids == null)
    {
      // There are no events
      $this->events = [];
    }
    else
    {
      foreach ($this->events_ids as $event_id)
      {
        $event = new Event($event_id);

        // Make sure the Event instance does not contain errors.
        if ($event->get_errors())
        {
          $d = "Error (Event ID " . $event->get_id() . ": " . $event->get_errors();
          $this->set_error($d);
        }
        else
        {
          if ($event->get_warnings())
          {
            $d = "Warning (Event ID " . $event->get_id() . ": " . $event->get_warnings();
            $this->set_warning($d);
          }
          array_push($this->events, $event);
        }
      }
    }
  }
}

?>
