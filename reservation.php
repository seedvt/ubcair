<?php

// These stuff are needed (for now) to connect Oracle, will figure out how to import from
// main php file
$success = True; //keep track of errors so it redirects the page only if there are no errors
$db_conn = OCILogon("ora_b4s8", "a16894123", "ug");
	
//include "flightdetails.php";
include "oci_functions.php";

if ($db_conn) {
 	if (array_key_exists('flightchoice', $_POST)) {
 		// test YVR to TPE (country TW) in flights.php it has 1 and 2 transfers
 		// direct flight I always test with YVR to HKG (country HK) but doesnt matter
		$flight = $_POST['flightchoice'];
		print_r($flight);
	}
}
?>