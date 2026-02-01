<?php
require_once __DIR__ . "/config.php";

$headers = getallheaders();

if (!isset($headers['x-api-key'])) {
    http_response_code(403);
    echo "API_KEY_MISSING";
    exit;
}

if ($headers['x-api-key'] !== API_KEY) {
    http_response_code(403);
    echo "API_KEY_INVALID";
    exit;
}
