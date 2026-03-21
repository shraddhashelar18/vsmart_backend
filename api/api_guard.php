<?php

require_once "../config.php"; // ✅ IMPORTANT

header("Content-Type: application/json");
require_once("../config.php");
/* ======================
   GET AUTH HEADER (FIXED)
====================== */

$authHeader = null;

/* Method 1: Standard */
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
}

/* Method 2: Apache fix */
if (!$authHeader && function_exists('apache_request_headers')) {
    $headers = apache_request_headers();

    foreach ($headers as $key => $value) {
        if (strtolower($key) == 'authorization') {
            $authHeader = $value;
            break;
        }
    }
}

/* Method 3: Fallback (IMPORTANT FOR LIVE SERVER) */
if (!$authHeader && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
}

/* ======================
   CHECK HEADER
====================== */
if (!$authHeader && isset($_GET['token'])) {
    $authHeader = "Bearer " . $_GET['token'];
}

if (!$authHeader) {
    echo json_encode([
        "status" => false,
        "message" => "Authorization header missing"
    ]);
    exit;
}

/* ======================
   EXTRACT TOKEN
====================== */

if (!str_starts_with($authHeader, "Bearer ")) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid Authorization format"
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

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid Token"
    ]);
    exit;
}

$user = $result->fetch_assoc();

/* ======================
   GLOBAL USER VARIABLES
====================== */

$currentUserId = $user['user_id'];
$currentRole = $user['role'];