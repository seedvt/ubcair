<?php

include('oci_functions.php');
?>
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
	}
	else {
		echo "<li><a href='logout.php'>Logout</a></li>";
		echo "<li><a href='support.php'>My Orders</a></li>";		
	}   
?>    
            <li><a href='flights.php'>Find flights</a></li>
        </ul>
    </div>
</div>

	<div class="banner">
    <h1 class="banner-head">
        Welcome to UBC Air
    </h1>
</div>

<?php

if (isset($_COOKIE["loggedin"])) {
	echo "Welcome " . $_COOKIE["cname"] . "!<br>";
}
else {
	// Redirect to the login page
	header('location: login.php');
}
/*
	echo "<div class='content-customer-area'>";
	echo "<p>Welcome, ".$_COOKIE['cname']
				."! You are customer id #".$_COOKIE['cid']
				.". Your email address is ".$_COOKIE['email'];
	echo "<br>This is the customer support area. Please select your action from the menu above</p></div>";	

 */
?>
</body>