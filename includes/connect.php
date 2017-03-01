<?php

/**
*	Connect to Database. Include this at the beginning of all relevant files.
*/

// Set connection variables
$host = "localhost";
$username = "admin";
$password = "";
$dbase = "testdb";

$connection = @mysqli_connect($host, $username, $password, $dbase) or die("ERROR: Unable to connect to database: " . mysqli_connect_error());

echo "<div class='connect-message'>
		<p>You are connected to the database " . $dbase . ".</p>
	</div>";

?>