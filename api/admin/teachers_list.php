<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

// GET filter
$class = trim($_GET['class'] ?? '');

if ($class === '') {
    echo json_encode([
        "status" => false,
        "message" => "Class is required"
    ]);
    exit;
}

/*
 Fetch teachers teaching a specific class
 Each teacher may have multiple subjects
*/
$sql = "
SELECT 
    u.user_id,
    t.employee_id,
    t.full_name,
    t.mobile_no,
    GROUP_CONCAT(DISTINCT s.subject_name SEPARATOR ', ') AS subjects
FROM teacher_assignments ta
JOIN users u ON u.user_id = ta.user_id
JOIN teachers t ON t.user_id = u.user_id
LEFT JOIN subjects s ON s.id = ta.subject_id
WHERE ta.class = ?
  AND u.role = 'teacher'
  AND u.status = 'approved'
GROUP BY u.user_id
ORDER BY t.full_name ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $class);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "status" => true,
    "class" => $class,
    "count" => count($data),
    "teachers" => $data
]);