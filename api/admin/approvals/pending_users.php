<?php
require_once "../../config.php";
require_once "../../api_guard.php";

header("Content-Type: application/json");

$sql = "
SELECT 
    u.user_id,
    u.email,
    u.role,
    u.status,
    t.full_name,
    t.mobile_no
FROM users u
LEFT JOIN teachers t ON t.user_id = u.user_id
WHERE u.status='pending'
ORDER BY u.user_id DESC
";

$res = $conn->query($sql);
$data = [];

while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "status" => true,
    "count" => count($data),
    "users" => $data
]);