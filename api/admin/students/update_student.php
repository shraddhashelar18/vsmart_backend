<?php
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

ini_set('display_errors', 1);
error_reporting(E_ALL);

/* =========================
   READ INPUT
========================= */

$data = json_decode(file_get_contents("php://input"), true);

if (
    empty($data['name']) ||
    empty($data['phone']) ||
    empty($data['parentPhone']) ||
    empty($data['roll']) ||
    empty($data['enrollment'])
) {
    echo json_encode([
        "status" => false,
        "message" => "All fields required"
    ]);
    exit;
}

/* =========================
   START TRANSACTION
========================= */

$conn->begin_transaction();

try {

    /* =========================
       UPDATE STUDENT TABLE
    ========================= */

   $stmt = $conn->prepare("
    UPDATE students
    SET full_name=?,
        mobile_no=?,
        parent_mobile_no=?,
        roll_no=?,
        enrollment_no=?
    WHERE user_id=?
");

$stmt->bind_param(
    "sssssi",
    $data['name'],
    $data['phone'],
    $data['parentPhone'],
    $data['roll'],
    $data['enrollment'],
    $data['user_id']
);

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    /* =========================
       UPDATE PARENT LINK
    ========================= */

    $stmtParent = $conn->prepare("
        UPDATE parents
        SET enrollment_no=?
        WHERE mobile_no=?
    ");

    $stmtParent->bind_param(
        "ss",
        $data['enrollment'],
        $data['parentPhone']
    );

    $stmtParent->execute();

    $conn->commit();

    echo json_encode([
        "status" => true,
        "message" => "Student updated successfully"
    ]);

} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);
}