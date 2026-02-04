<?php
require_once "../config.php";
require_once "../api_guard.php";
header("Content-Type: application/json");

$res = $conn->query(
    "SELECT enrollment_no, full_name, class
     FROM students
     ORDER BY full_name"
);

$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "status"=>true,
    "students"=>$data
]);