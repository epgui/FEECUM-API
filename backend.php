<?php

include("class.dbconfig.php");
include("class.database.php");
include("class.event.php");
include("class.monthlyEvents.php");

$error         = false;
$error_message = "";
$events        = "";

// Read GET parameters and make sure they are valid
$requested_y = $_GET["year"];
$requested_m = $_GET["month"];

if (!$requested_y)
{
  $error         = true;
  $error_message = "Invalid year.";
}
else
{
  if (!$requested_m)
  {
    $error         = true;
    $error_message = "Invalid month.";
  }
  else
  {
    // Open a connection to MySQL
    $db = Database::getInstance();
    $db->connect_to_database($mysql_host, $mysql_user, $mysql_password, $mysql_database);

    // If connection is successful, get the list of events for the requested month
    if ($db->status != "Connected")
    {
      $error         = true;
      $error_message = $db->error_message;
    }
    else
    {
      $monthly_events = new MonthlyEvents($requested_y, $requested_m);
      $events = $monthly_events->get_events;
    }
  }
}

// Format everything nicely in json
json_encode(array("error" => $error, "error_message" => $error_message, "events" => $events));

$db->disconnect();

?>
