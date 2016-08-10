<?php

class MonthlyEvents
{
  protected $year_number;
  protected $month_number;
  protected $unix_start_of_month;
  protected $unix_end_of_month;
  protected $events_ids = [];
  protected $events = [];

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

  function get_year_number()
  {
    return $this->year_number;
  }

  function get_month_number()
  {
    return $this->month_number;
  }

  function get_events_ids()
  {
    return $this->events_ids;
  }

  function get_events()
  {
    return $this->events;
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
    if ($db->status == "Disconnected")
    {
      // Output error message
    }
    else
    {
      // Make sure query is successful
      if (!$result = $db->execute_query($q))
      {
        // Output error message
      }
      else
      {
        // Find all events for that month and format it into an array
        if ($result->num_rows <= 0)
        {
          // No results
        }
        else
        {
          while($row = $qresult->fetch_assoc())
          {
            array_push($this->events_ids, $row["evdet_id"]);
          }
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
        $event = new Event($event_id)
        array_push($this->events, $event);
      }
    }
  }
}

?>
