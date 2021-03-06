<!DOCTYPE html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="UBC Airline Booking Service">
	<title>UBC Air</title>
	<link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.5.0/pure-min.css">
	<link rel="stylesheet" href="css/mainpage.css"
</head>

<body>
<div class="header">
    <div class="home-menu pure-menu pure-menu-open pure-menu-horizontal pure-menu-fixed">
        <a class="pure-menu-heading" href="">UBC Air</a>
        <ul>

        	
            <li><a href='index.php'>Home</a></li>
<?php
	
	if(!array_key_exists('loggedin', $_COOKIE) ) {
		echo "<li><a href='login.php'>Login</a></li>";
		echo "<li><a href='register.php'>Sign Up</a></li>";
		header('location: login.php');
	}
	else {
		echo "<li><a href='logout.php'>Logout</a></li>";
		echo "<li><a href='support.php'>My Orders</a></li>";		
	}   
	
	if (array_key_exists('flightchoice', $_POST)) {
 		// test YVR to TPE (country TW) in flights.php it has 1 and 2 transfers
 		// direct flight I always test with YVR to HKG (country HK) but doesnt matter
		$res = unserialize($_POST['flightchoice']);
		//print_r($res);
	}
?>  
            <li><a href="flights.php">Find flights</a></li>      
        </ul>
    </div>
</div>

	<div class="banner">
    <h1 class="banner-head">
        Welcome to UBC Air
    </h1>
</div>
<div class='content-customer-area'><body>

<br>Your Reservation and Payment history

<!--Script for toggling flight details-->
<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
<script>
	$(document).ready(function(){
    	$(".toggler").click(function(e){
        	e.preventDefault();
       		$('.detail'+$(this).attr('detail-num')).toggle();
    	});
	});
</script>
<?php

include "oci_functions.php";
$success = True; //keep track of errors so it redirects the page only if there are no errors

function parseClass($class) {
	if (strcmp($class,"1") == 0) return "Economy";
	if (strcmp($class,"3") == 0) return "Business";
	if (strcmp($class,"5") == 0) return "First Class";	
}

function parseCard($cardNumber) {
	return substr($cardNumber, 0,3) . str_repeat("*",9) . substr($cardNumber, 12,4);
}
function printHistory($history) {	 
	echo "<table class ='pure-table pure-table-bordered'>";
	echo "<thead><tr><th>Reservation Id</th><th>Date of Departure (GMT)</th><th>Depart City</th>"
	   . "<th>Depart Country</th><th>Arrival City</th><th>Arrival Country</th>"
	   . "<th>Class</th><th>Number of tickets</th><th>Credit card #</th>"
	   . "<th>COST (CAD)</th></thead>";
	$it = 0;
	while ($tuple = OCI_Fetch_Array($history, OCI_ASSOC)) {
		$numFlights=1;
		if (array_key_exists("FID3", $tuple)) $numFlights=3;
		else if (array_key_exists("FID2", $tuple) && (array_key_exists("FID3", $tuple) != TRUE)) $numFlights=2;
		$details = getDetails($tuple,$numFlights);
		echo "<tr><td>".$tuple['RESID']."</td><td>".$details['DEPARTDATE']."</td><td>"
			.$details['DEPARTCITY']."</td><td>".$details['DEPARTCOUNTRY']."</td><td>"
			.$details['ARRIVALCITY']."</td><td>".$details['ARRIVALCOUNTRY']."</td><td>"
			.parseClass($tuple['PCLASS'])."</td><td>".$tuple['TICKET_NUM']."</td><td>"
			.parseCard($tuple['CREDITCARD'])."</td><td>".$tuple['TOTAL_COST']."</td></tr>";
		echo "<tr><td>";
		$flight = Array("FIRSTID" => $tuple['FID1']);
		if ($numFlights >= 2) $flight["SECONDID"] = $tuple['FID2'];
		if ($numFlights == 3) $flight["THIRDID"] = $tuple['FID3'];
		printDetails($flight, $it, 1);
		echo "</td></tr>";
		$it++;	 
	}
	echo "</table>";
}

function getDetails($bigTuple,$numFlights) {
	$fid1 = $bigTuple['FID1'];
	$departFlight = oci_fetch_assoc(executePlainSQL("select * from Flight where fid='$fid1'"));
	$departDate = parseDate($departFlight['DEPARTTIME'],1);
	$departApCode = $departFlight['DEPARTAP'];
	$departAp = oci_fetch_assoc(executePlainSQL("select CITY, COUNTRY from Airport where code='$departApCode'"));
	$departCity = $departAp['CITY'];
	$departCountry = $departAp['COUNTRY'];
	if ($numFlights == 1) $arrivalFlight = $departFlight;
	else if ($numFlights == 2) {
		$fid2 = $bigTuple['FID2'];
		$arrivalFlight = oci_fetch_assoc(executePlainSQL("select * from Flight where fid='$fid2'"));
	}
	else {
		$fid3 = $bigTuple['FID3'];
		$arrivalFlight = oci_fetch_assoc(executePlainSQL("select * from Flight where fid='$fid3'"));
	}
	$arrivalApCode = $arrivalFlight['ARRIVALAP'];
	$arrivalAp = oci_fetch_assoc(executePlainSQL("select * from Airport where code='$arrivalApCode'"));
	$arrivalCity = $arrivalAp['CITY'];
	$arrivalCountry = $arrivalAp['COUNTRY'];
	$flightLoc = Array("DEPARTDATE" => $departDate, "DEPARTCITY" => $departCity, "DEPARTCOUNTRY" => $departCountry,
					"ARRIVALCITY" => $arrivalCity, "ARRIVALCOUNTRY" => $arrivalCountry);   
	return $flightLoc;
}
// Connect Oracle...
if ($db_conn) {
	$cid = $_COOKIE['cid'];
	$history = executePlainSQL("select m.resid,fid1,fid2,fid3,pclass,ticket_num,creditcard,total_cost 
							   from deter_pay d, payment p,make_res m,
								  (select i1.resid, i1.fid as fid1,fid2,fid3 
								   from (select * from res_includes where resorder=1) i1 
								   left join (select i2.fid as fid2, i3.fid as fid3,i2.resid 
								              from (select * from res_includes where resorder=2) i2 
								              left join (select * from res_includes where resorder=3) i3 
								              on i2.resid=i3.resid) i4
							   	   on i1.resid=i4.resid) f 
							   where m.resid=f.resid AND d.resid=m.resid AND p.payid=d.payid AND m.cid='$cid'
							   order by m.resid");
	printHistory($history);
	OCILogoff($db_conn);
} else {
	echo "cannot connect";
	$e = OCI_Error(); // For OCILogon errors pass no handle
	echo htmlentities($e['message']);
}
?>
</div>
</body>