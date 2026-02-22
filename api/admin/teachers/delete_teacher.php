<?php

/* =====================================
   IMPORT REQUIRED FILES
===================================== */

require_once("../config.php");
require_once("../api_guard.php");

/* =====================================
   SET RESPONSE TYPE
===================================== */

header("Content-Type: application/json");

/* =====================================
   GET JSON BODY
===================================== */

$data = json_decode(file_get_contents("php://input"), true);

$id = intval($data['id'] ?? 0);

/* =====================================
   VALIDATION
===================================== */

if ($id <= 0) {
    echo json_encode([
        "status" => false,
        "message" => "Teacher ID required"
    ]);
    exit;
}

/* =====================================
   START TRANSACTION
===================================== */

$conn->begin_transaction();

try {

    /* 1️⃣ DELETE FROM teacher_assignments */
    $stmt1 = $conn->prepare("
        DELETE FROM teacher_assignments
        WHERE user_id = ?
    ");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();

    /* 2️⃣ DELETE FROM teachers */
    $stmt2 = $conn->prepare("
        DELETE FROM teachers
        WHERE user_id = ?
    ");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();

    /* 3️⃣ DELETE FROM users (IMPORTANT) */
    $stmt3 = $conn->prepare("
        DELETE FROM users
        WHERE user_id = ?
    ");
    $stmt3->bind_param("i", $id);
    $stmt3->execute();

    /* COMMIT */
    $conn->commit();

    echo json_encode([
        "status" => true,
        "message" => "Teacher deleted successfully"
    ]);

} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "status" => false,
        "message" => "Delete failed"
    ]);
}