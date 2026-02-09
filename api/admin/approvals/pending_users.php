<?php
require_once "../../config.php";
require_once "../../api_guard.php";

header("Content-Type: application/json");

$res = $conn->query("
    SELECT user_id, email, role, created_at
    FROM users
    WHERE status = 'pending'
    ORDER BY created_at DESC
");

$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "status" => true,
    "users" => $data
]);