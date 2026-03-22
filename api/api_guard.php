<?php

require_once(__DIR__ . "/config.php");

header("Content-Type: application/json");

/* ======================
   GET AUTH HEADER
====================== */

$authHeader = null;

if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
}

if (!$authHeader && function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    foreach ($headers as $key => $value) {
        if (strtolower($key) == 'authorization') {
            $authHeader = $value;
            break;
        }
    }
}

if (!$authHeader && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
}

/* fallback for ?token */
if (!$authHeader && isset($_GET['token'])) {
    $authHeader = "Bearer " . $_GET['token'];
}

if (!$authHeader) {
    echo json_encode([
        "status" => false,
        "message" => "Authorization missing"
    ]);
    exit;
}

if (!str_starts_with($authHeader, "Bearer ")) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid format"
    ]);
    exit;
}

$token = substr($authHeader, 7);

/* ======================
   VALIDATE TOKEN
====================== */

$stmt = $conn->prepare("
    SELECT user_id, role
    FROM users
    WHERE auth_token = ?
");

$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid token"
    ]);
    exit;
}

$stmt->bind_result($currentUserId, $currentRole);
$stmt->fetch();