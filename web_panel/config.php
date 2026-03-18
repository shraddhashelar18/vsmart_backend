
<?php

$host = "mysql.hostinger.in";
$user = "u107985738_vsmart";
$pass = "Vsmart@0568";
$db   = "u107985738_vsmart";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed");
}
?>