<?php
$conn = new mysqli("localhost", "root", "", "vsmart");

if ($conn->connect_error) {
    die("Database connection failed");
}
?>
