<?php

// Import the keys/secret variables
include("../../keys.php");

// Set the header Content-Type for JSON
header('Content-Type: application/json');

// Open connection to database using variables set in keys
$dbconn = pg_connect("host=" . $dbhost . " port=". $dbport . " dbname=" . $dbname . " user=" . $dbuser . " password=" . $dbpass) or die(return_error("Could not connect to database.", pg_last_error()));

// Get the device ID from the URL parameter
$device = $_GET['device'];

// Get the season from the URL parameter
$season = $_GET['season'];

// When three distinct pm value is available
$sensor_name='AirBeam2-PM2.5';

// When only one pm value available for session
$sensor_name_comm='AirBeam-PM';

// Build the SQL query
$query = "SELECT airterrier.time AS x, measured_value AS y,comments||' generated by '||pollutant_source AS text,(case when pollutant_source is null then 'rgb(0,0,255,0.5)' else 'rgb(255,0,0,0.5)' end) AS color FROM airterrier left join mobile_monitoring on date_trunc('minute',airterrier.time)=date_trunc('minute',mobile_monitoring.date+mobile_monitoring.time) and airterrier.community=mobile_monitoring.community and airterrier.season=mobile_monitoring.season
WHERE measurement_type = 'Particulate Matter' AND session_title = $1 AND airterrier.season = $2 AND flag is null AND (sensor_name=$3 OR sensor_name=$4) ORDER BY airterrier.time";

// Run the query
$result = pg_query_params($dbconn, $query, array($device, $season,$sensor_name,$sensor_name_comm)) or die (return_error("Query failed.", pg_last_error()));

// Create JSON result
$resultArray = pg_fetch_all($result);

// Seperate out the X and Y (Time and values) data so that it can be charted by plot.ly
$xarray = [];
$yarray = [];
$textarray = [];
$color = [];

foreach($resultArray as $item) {
	$xarray[] = substr($item['x'], 0, -3);
	$yarray[] = floatval($item['y']);
	$textarray[] = $item['text'];
	$color[] = $item['color'];
}

// Build the return array with X, Y, type, and name for plot.ly
$returnarray = ["x" => $xarray, "y" => $yarray, "text" => $textarray, "color" => $color,  "mode" => "markers", "type" => "scatter", "name" => "PM<sub>2.5</sub> (&mu;g/m<sup>3</sup>)"];

// Encode the array as JSON and return it.
echo json_encode($returnarray);

// Free resultset
pg_free_result($result);

// Closing connection
pg_close($dbconn);

function return_error($error_description, $error_details) {
	return '{"error":"' . $error_description . '","error_details":"' . $error_details .'"}';
}
?>
