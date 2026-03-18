<?php

$host = "mysql.hostinger.in";
$user = "u107985738_vsmart";
$pass = "Vsmart@0568";
$db   = "u107985738_vsmart";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode([
        "status" => false,
        "message" => "Database connection failed"
    ]));
}

define("BASE_URL","http://192.168.1.138/vsmart_backend/");
?>