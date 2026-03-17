<?php
require_once("../../config.php");
require_once("../../api_guard.php");
require_once("../../cors.php");

header("Content-Type: application/json");

$result = $conn->query("
SELECT ct1_published, ct2_published
FROM settings
LIMIT 1
");

$row = $result->fetch_assoc();

echo json_encode([
    "status" => true,
    "ct1" => (int) $row["ct1_published"],
    "ct2" => (int) $row["ct2_published"]
]);