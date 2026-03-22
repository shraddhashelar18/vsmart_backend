<?php
require_once("../../api_guard.php");
require_once("../../config.php");
require_once("../../cors.php");

header("Content-Type: application/json");

if ($currentRole != "admin") {
    echo json_encode(["status" => false, "message" => "Access denied"]);
    exit;
}

/* 🔥 SAFE SETTINGS FETCH */
$cycle = "EVEN"; // default

$setting = $conn->query("SELECT active_cycle FROM settings LIMIT 1");

if ($setting && $setting->num_rows > 0) {
    $row = $setting->fetch_assoc();
    if (isset($row['active_cycle'])) {
        $cycle = $row['active_cycle'];
    }
}

/* 🔥 FETCH CLASSES */
$result = $conn->query("SELECT class_name FROM classes ORDER BY class_name");

if (!$result) {
    echo json_encode(["status" => false, "message" => "Class query failed"]);
    exit;
}

$classes = [];

while ($c = $result->fetch_assoc()) {

    $class = $c['class_name'];

    preg_match('/\d+/', $class, $match);
    $sem = isset($match[0]) ? (int) $match[0] : 0;

    if (
        ($cycle == "EVEN" && $sem % 2 == 0) ||
        ($cycle == "ODD" && $sem % 2 != 0)
    ) {
        $classes[] = $class;
    }
}

echo json_encode([
    "status" => true,
    "cycle" => $cycle,
    "classes" => $classes
]);