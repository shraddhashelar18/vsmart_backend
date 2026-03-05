<?php

/* =====================================
   IMPORT REQUIRED FILES
===================================== */

require_once("/../../config.php");
require_once("/../../api_guard.php");

/* =====================================
   RESPONSE TYPE
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
   START TRANSACTION (IMPORTANT)
===================================== */

$conn->begin_transaction();

try {

    /* ==============================
       DELETE FROM CHILD TABLE FIRST
    =============================== */

    $deleteAssignments = $conn->prepare(
        "DELETE FROM teacher_assignments WHERE user_id=?"
    );
    $deleteAssignments->bind_param("i",$id);
    $deleteAssignments->execute();

    /* ==============================
       DELETE FROM TEACHERS TABLE
    =============================== */

    $deleteTeacher = $conn->prepare(
        "DELETE FROM teachers WHERE user_id=?"
    );
    $deleteTeacher->bind_param("i",$id);
    $deleteTeacher->execute();

    /* ==============================
       DELETE FROM USERS TABLE
    =============================== */

    $deleteUser = $conn->prepare(
        "DELETE FROM users WHERE user_id=?"
    );
    $deleteUser->bind_param("i",$id);

    if(!$deleteUser->execute()){
        throw new Exception($deleteUser->error);
    }

    /* =====================================
       COMMIT TRANSACTION
    ====================================== */

    $conn->commit();

    echo json_encode([
        "status" => true,
        "message" => "Teacher deleted successfully"
    ]);

} catch (Exception $e) {

    /* =====================================
       ROLLBACK IF ANY ERROR
    ====================================== */

    $conn->rollback();

    echo json_encode([
        "status" => false,
        "message" => "Delete failed: " . $e->getMessage()
    ]);
}