<?php
header("Content-Type: application/json");

$VALID_API_KEY = "VSMART_API_2026";

// 🔹 Read API key from headers
$headers = getallheaders();
$apiKey  = $headers['x-api-key'] ?? $headers['X-API-KEY'] ?? '';

if ($apiKey !== $VALID_API_KEY) {
    http_response_code(403);
    echo json_encode([
        "status" => false,
        "message" => "Invalid or missing API key"
    ]);
    exit;
}
