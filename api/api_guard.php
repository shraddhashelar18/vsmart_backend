<?php

$headers = getallheaders();

$api_key = $headers['x-api-key'] ?? '';

$valid_key = "VSMART_API_2026";   // 🔐 YOUR SECRET KEY

if ($api_key !== $valid_key) {
    echo json_encode([
        "status" => false,
        "message" => "Unauthorized Access - Invalid API Key"
    ]);
    exit;
}
?> 