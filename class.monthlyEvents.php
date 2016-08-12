<?php

class MonthlyEvents
{
  protected $year_number;
  protected $month_number;
  public $unix_start_of_month;
  public $unix_end_of_month;
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
    $this->populate_events_ids();

    // Populate list of every event requested
    $this->populate_events();

    // Get a list of all event repeats within the month
    $repeat_events = $this->find_repeat_events();

    if ($repeat_events)
    {
      // Merge the two lists of event ids
      $this->events = array_merge($this->events, $repeat_events);
    }
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

  private function set_error($d)
  {
    array_push($this->warnings, $d);
  }

  protected function compute_unix_timestamps()
  {
    $year = $this->year_number;
    $month = $this->month_number;

    $this->unix_start_of_month = mktime( 0,  0,  0, $month,     1, $year);
    $this->unix_end_of_month   = mktime(23, 59, 00, $month + 1, 0, $year);

    return null;
  }

  protected function populate_events_ids()
  {
    // Build MySQL query
    $q  = "SELECT evdet_id FROM x8g2f_jevents_vevdetail";
    $q .= " WHERE dtstart >= " . $this->unix_start_of_month;
    $q .= " AND dtstart <= " . $this->unix_end_of_month;
    $q .= " ORDER BY dtstart ASC";

    // Find the database connection
    $db = Database::getInstance();

    // Check connection status
    if ($db->get_status() == "Disconnected")
    {
      if ($db->get_error_message())
      {
        $d = "Database connection error: " . $db->get_error_message();
        $this->set_error($d);
        return false;
      }
      else
      {
        $d = "Unspecified database connection error.";
        $this->set_error($d);
        return false;
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
          return false;
        }
        else
        {
          $d = "MySQL query returned an unspecified error.";
          $this->set_error($d);
          return false;
        }
      }
      else
      {
        // Find all events for that month and format it into an array
        if ($result->num_rows == 0)
        {
          $d = "No events were found for this month.";
          $this->set_warning($d);
          $result->free();
          return true;
        }
        else
        {

          while ($row = $result->fetch_assoc())
          {
            array_push($this->events_ids, $row["evdet_id"]);
          }

          $result->free();

          return true;
        }
      }
    }
  }

  private function find_repeat_events()
  {
    $repeat_events_records = [];

    // Build MySQL query
    $q  = "SELECT * FROM x8g2f_jevents_repetition";
    $q .= " WHERE startrepeat";
    $q .= " BETWEEN '" . $this->futfre($this->unix_start_of_month) . "'";
    $q .= " AND '"     . $this->futfre($this->unix_end_of_month) . "'";
    $q .= " ORDER BY startrepeat ASC";

    // Find the database connection
    $db = Database::getInstance();

    // Check connection status
    if ($db->get_status() == "Disconnected")
    {
      if ($db->get_error_message())
      {
        $d = "Database connection error: " . $db->get_error_message();
        $this->set_error($d);
        return false;
      }
      else
      {
        $d = "Unspecified database connection error.";
        $this->set_error($d);
        return false;
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
          return false;
        }
        else
        {
          $d = "MySQL query returned an unspecified error.";
          $this->set_error($d);
          return false;
        }
      }
      else
      {
        // Find all events for that month and format it into an array
        if ($result->num_rows == 0)
        {
          $d = "No events were found for this month.";
          $this->set_warning($d);
          $result->free();
          return false;
        }
        else
        {
          while($row = $result->fetch_assoc())
          {
            array_push($repeat_events_records, $row);
          }

          $result->free();

          return $this->populate_repeat_events($repeat_events_records);
        }
      }
    }
  }

  private function futfre($unix_timestamp)
  {
    $date = new DateTime();
    $date->setTimestamp($unix_timestamp);
    return utf8_encode($date->format('Y-m-d H:i:s'));
  }

  private function populate_events()
  {
    if ($this->events_ids == [])
    {
      $d = "Warning: there are no events for this month.";
      $this->set_warning($d);
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

          $array_event = [ "id"          => $event->get_id(),
                           "t_start"     => $this->futfre($event->get_unix_timestamp_start()),
                           "t_end"       => $this->futfre($event->get_unix_timestamp_end()),
                           "category"    => $event->get_category(),
                           "summary"     => $event->get_summary(),
                           "description" => $event->get_description(),
                           "warnings"    => $event->get_warnings(),
                           "errors"      => $event->get_errors() ];

          array_push($this->events, $array_event);
        }
      }
    }
  }

  private function populate_repeat_events($repeat_events_records)
  {
    $repeat_events = [];

    if ($repeat_events_records == [])
    {
      $d = "Warning: there are no repeat events for this month.";
      $this->set_warning($d);
    }
    else
    {
      foreach ($repeat_events_records as $repeat_event_record)
      {
        $repeat_event_id      = $repeat_event_record["eventdetail_id"];
        $unix_timestamp_start = utf8_encode(strtotime($repeat_event_record["startrepeat"]));
        $unix_timestamp_end   = utf8_encode(strtotime($repeat_event_record["endrepeat"]));

        $repeat_event = new Event($repeat_event_id);

        $array_repeat_event = [ "id"          => $repeat_event_id,
                                "t_start"     => $this->futfre($unix_timestamp_start),
                                "t_end"       => $this->futfre($unix_timestamp_end),
                                "category"    => $repeat_event->get_category(),
                                "summary"     => $repeat_event->get_summary(),
                                "description" => $repeat_event->get_description(),
                                "warnings"    => $repeat_event->get_warnings(),
                                "errors"      => $repeat_event->get_errors() ];

        array_push($repeat_events, $array_repeat_event);
      }
    }

    return $repeat_events;
  }
}

?>
