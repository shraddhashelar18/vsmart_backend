<?php
require_once "../config.php";
require_once "../api_guard.php";

$total = $conn->query("SELECT COUNT(*) c FROM students")->fetch_assoc()['c'];
$with_parent = $conn->query(
    "SELECT COUNT(*) c FROM students WHERE parent_mobile_no IS NOT NULL"
)->fetch_assoc()['c'];

echo json_encode([
    "status"=>true,
    "total_students" => (int)$total,
    "with_parents" => (int)$with_parent,
    "without_parents" => (int)($total - $with_parent)
]);
