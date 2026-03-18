<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "START<br>";

$host = "mysql.hostinger.in"; // 🔥 IMPORTANT: use localhost on Hostinger
$user = "u107985738_vsmart";
$pass = "Vsmart@0568"; // ❗ put correct password
$db   = "u107985738_vsmart";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("DB ERROR: " . $conn->connect_error);
}

echo "DB CONNECTED";
?>