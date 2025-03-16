<?php

// Database connection details
$servername = "nsc5531.encs.concordia.ca";
$username = "nsc55314";
$password = "RpMKHa25";  
$database = "nsc55314";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>

<!DOCTYPE html>

<html>
 <head>
 <title>Date/Time Functions Demo</title>
 </head>
 <body>
 <h1>Date/Time Functions Demo</h1>
 <p>The current date and time is 
<em><?echo date("D M d, Y H:i:s", time())?></em>
 <p>Current PHP version:
 <em><?echo phpversion()?></em>
 </body>
 </html>
