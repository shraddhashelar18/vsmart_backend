<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

$department = $_POST['department'] ?? '';
$year       = $_POST['year'] ?? '';

if ($department === '' || $year === '') {
    echo json_encode([
        "status" => false,
        "message" => "Department and year required"
    ]);
    exit;
}

$yearMap = [
    "FY" => "1",
    "SY" => "2",
    "TY" => "3"
];

$yearCode = $yearMap[$year];

$classes = [
    $department . $yearCode . "KA",
    $department . $yearCode . "KB",
    $department . $yearCode . "KC"
];

echo json_encode([
    "status" => true,
    "classes" => $classes
]);