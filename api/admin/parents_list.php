<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

$search = trim($_GET['search'] ?? '');

$where = "";
if ($search !== '') {
    $search = $conn->real_escape_string($search);
    $where = "WHERE 
        p.full_name LIKE '%$search%' OR
        u.email LIKE '%$search%' OR
        p.mobile_no LIKE '%$search%' OR
        p.enrollment_no LIKE '%$search%'";
}

$sql = "
SELECT
    p.user_id,
    p.full_name,
    u.email,
    p.mobile_no,
    p.enrollment_no
FROM parents p
JOIN users u ON u.user_id = p.user_id
$where
ORDER BY p.user_id DESC
";

$res = $conn->query($sql);

$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "status" => true,
    "count" => count($data),
    "parents" => $data
]);