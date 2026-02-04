<?php
// DATABASE
$conn = new mysqli("localhost", "root", "", "vsmart");
if ($conn->connect_error) {
    die("DB Connection Failed");
}

// API KEY
define("API_KEY", "VSMART_API_2026");
