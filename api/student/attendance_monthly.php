<?php
require_once "../config.php";
require_once "../api_guard.php";
header("Content-Type: application/json");

$student_id = $_POST['student_id'];

$stmt = $conn->prepare("
    SELECT MONTH(date) as month,
           SUM(status='present') as present
    FROM attendance
    WHERE student_id = ?
    GROUP BY MONTH(date)
");
$stmt->bind_param("i", $student_id);
$stmt->execute();

echo json_encode([
    "status"=>true,
    "data"=>$stmt->get_result()->fetch_all(MYSQLI_ASSOC)
]);