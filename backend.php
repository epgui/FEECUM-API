<?php

// Remove this in production:
ini_set('display_errors', 1);

// Add this in production:
// header('Content-Type: application/json; charset=utf-8');

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

if (!isset($requested_y))
{
  $error         = true;
  $error_message = "Invalid year.";
}
else
{
  if (!isset($requested_m))
  {
    $error         = true;
    $error_message = "Invalid month.";
  }
  else
  {
    // Open a connection to MySQL
    $db = Database::getInstance();
    $db->connect_to_database();

    // Check if connection is successful
    if ($db->get_status() != "Connected")
    {
      $error         = true;
      $error_message = $db->error_message;
    }
    else
    {
      // Get the list of events for the requested month
      $monthly_events = new MonthlyEvents($requested_y, $requested_m);
      $events = $monthly_events->get_events();
    }

    $db->disconnect();
  }
}

// Format everything nicely in json
$json = json_encode(array("error" => $error, "error_message" => $error_message, "events" => $events), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// Output result
echo($json);

?>
