<?php
require_once("../../config.php");
require_once("../../api_guard.php");
require_once("../../cors.php");

header("Content-Type: application/json");

/* ================= ROLE CHECK ================= */

if ($currentRole != "admin") {
    echo json_encode([
        "status" => false,
        "message" => "Access Denied"
    ]);
    exit;
}

/* ================= INPUT ================= */

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['allowMarksheetUpload'])) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid data"
    ]);
    exit;
}

$allowMarksheetUpload = $data['allowMarksheetUpload'] ? 1 : 0;

/* ================= UPDATE SETTINGS ================= */

$stmt = $conn->prepare("
UPDATE settings
SET allow_marksheet_upload = ?
WHERE id = 1
");

$stmt->bind_param("i", $allowMarksheetUpload);

if ($stmt->execute()) {

    echo json_encode([
        "status" => true,
        "allowMarksheetUpload" => (bool)$allowMarksheetUpload,
        "message" => "Result control updated successfully"
    ]);

} else {

    echo json_encode([
        "status" => false,
        "message" => "Update failed"
    ]);
}