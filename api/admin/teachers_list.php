<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

$search = trim($_GET['search'] ?? '');

$where = "
WHERE u.role = 'teacher'
AND u.status = 'approved'
";

if ($search !== '') {
    $search = $conn->real_escape_string($search);
    $where .= " AND (
        t.full_name LIKE '%$search%' OR
        u.email LIKE '%$search%' OR
        t.mobile_no LIKE '%$search%'
    )";
}

$sql = "
SELECT 
    u.user_id,
    t.employee_id,
    t.full_name,
    u.email,
    t.mobile_no
FROM teachers t
JOIN users u ON u.user_id = t.user_id
$where
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
    "teachers" => $data
]);
