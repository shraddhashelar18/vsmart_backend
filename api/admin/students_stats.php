<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

$total = $conn->query("SELECT COUNT(*) c FROM students")->fetch_assoc()['c'];
$with_parent = $conn->query(
    "SELECT COUNT(*) c FROM students WHERE parent_mobile_no IS NOT NULL"
)->fetch_assoc()['c'];

$without_parent = $total - $with_parent;

echo json_encode([
    "status" => true,
    "total_students" => $total,
    "with_parents" => $with_parent,
    "without_parents" => $without_parent
]);