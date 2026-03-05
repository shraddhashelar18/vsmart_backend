<?php
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data['user_id'];

$stmt = $conn->prepare("
    SELECT subject, exam_type, obtained_marks, total_marks
    FROM marks
    WHERE student_id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$reports = [];

while ($row = $result->fetch_assoc()) {
    $reports[] = $row;
}

echo json_encode([
    "status" => true,
    "reports" => $reports
]);