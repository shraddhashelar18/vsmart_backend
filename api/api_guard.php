<?php

$headers = getallheaders();

/* Fix for Apache not passing Authorization header */

$authHeader = null;

if (isset($headers['Authorization'])) {
    $authHeader = $headers['Authorization'];
} elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
}

if (!$authHeader) {
    echo json_encode([
        "status" => false,
        "message" => "Authorization header missing"
    ]);
    exit;
}

/* Extract token */

$token = str_replace("Bearer ", "", $authHeader);