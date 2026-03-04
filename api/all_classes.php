<?php

require_once("config.php");
require_once("api_guard.php");

header("Content-Type: application/json");

/* =========================
   METHOD CHECK
========================= */

if($_SERVER['REQUEST_METHOD'] !== 'GET'){
    echo json_encode([
        "status" => false,
        "message" => "Invalid request method"
    ]);
    exit;
}

/* =========================
   FETCH ALL CLASSES
========================= */

$result = $conn->query("
SELECT class_name
FROM classes
ORDER BY class_name
");

$classes = [];

while($row = $result->fetch_assoc()){
    $classes[] = $row['class_name'];
}

/* =========================
   RESPONSE
========================= */

echo json_encode([
    "status" => true,
    "classes" => $classes
]);