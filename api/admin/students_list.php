<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

$search = trim($_GET['search'] ?? '');

$where = "
WHERE u.role = 'student'
AND u.status = 'approved'
";

if ($search !== '') {
    $search = $conn->real_escape_string($search);
    $where .= " AND (
        s.full_name LIKE '%$search%' OR
        u.email LIKE '%$search%' OR
        s.mobile_no LIKE '%$search%' OR
        s.class LIKE '%$search%'
    )";
}

$sql = "
SELECT 
    u.user_id,
    s.full_name,
    u.email,
    s.mobile_no,
    s.class
FROM students s
JOIN users u ON u.user_id = s.user_id
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
    "students" => $data
]);
