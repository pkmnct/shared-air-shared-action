<?php

// Import the keys/secret variables
include("../../keys.php");

// Set the header Content-Type for JSON
header('Content-Type: application/json');

// Open connection to database using variables set in keys
$dbconn = pg_connect("host=" . $dbhost . " port=". $dbport . " dbname=" . $dbname . " user=" . $dbuser . " password=" . $dbpass) or die(return_error("Could not connect to database.", pg_last_error()));

// Get the device ID from the URL parameter
$route = $_GET['route'];

// Get the season from the URL parameter
$season = $_GET['season'];
// When three distinct pm value is available
$sensor_name='AirBeam2-PM2.5';

// When only one pm value available for session
$sensor_name_comm='AirBeam-PM';

// Build the SQL query
$query = "SELECT latitude, longitude, measured_value as data FROM airterrier WHERE measurement_type = 'Particulate Matter' AND session_title = $1 AND season = $2 AND (sensor_name=$3 OR sensor_name=$4) AND flag is null  ORDER BY time";

// Run the query
$result = pg_query_params($dbconn, $query, array($route, $season, $sensor_name, $sensor_name_comm)) or die (return_error("Query failed.", pg_last_error()));

// Create JSON result
$resultArray = pg_fetch_all($result);

// Encode the array as JSON and return it.
echo json_encode($resultArray);

// Free resultset
pg_free_result($result);

// Closing connection
pg_close($dbconn);

function return_error($error_description, $error_details) {
	return '{"error":"' . $error_description . '","error_details":"' . $error_details .'"}';
}
?>
