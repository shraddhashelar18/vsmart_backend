<?php
require_once "../api_guard.php";
header("Content-Type: application/json");

echo json_encode([
    "status" => true,
    "years" => ["FY", "SY", "TY"]
]);