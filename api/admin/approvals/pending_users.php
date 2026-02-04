<?php
require_once "../../config.php";
require_once "../../api_guard.php";

header("Content-Type: application/json");

$sql = "
SELECT 
    user_id,
    email,
    role,
    status,
    created_at
FROM users
WHERE status = 'pending'
ORDER BY created_at DESC
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
