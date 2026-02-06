<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

echo json_encode([
    "status" => true,
    "departments" => [
        ["code" => "IF", "name" => "IT Department"],
        ["code" => "CO", "name" => "CO Department"],
        ["code" => "EJ", "name" => "EJ Department"]
    ]
]);