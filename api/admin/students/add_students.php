<?php

/* =====================================
   IMPORT REQUIRED FILES
===================================== */

require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

/* =====================================
   SHOW ERRORS (REMOVE IN PRODUCTION)
===================================== */

ini_set('display_errors', 1);
error_reporting(E_ALL);

/* =====================================
   READ JSON INPUT
===================================== */

$data = json_decode(file_get_contents("php://input"), true);

/* =====================================
   VALIDATION
===================================== */

if (
    empty($data['name']) ||
    empty($data['email']) ||
    empty($data['password']) ||
    empty($data['phone']) ||
    empty($data['parentPhone']) ||
    empty($data['roll']) ||
    empty($data['enrollment']) ||
    empty($data['class'])
) {
    echo json_encode([
        "status" => false,
        "message" => "All fields required"
    ]);
    exit;
}

/* =====================================
   START TRANSACTION
===================================== */

$conn->begin_transaction();

try {

    /* ===============================
       1️⃣ CHECK DUPLICATE EMAIL
    =============================== */

    $checkEmail = $conn->prepare("SELECT user_id FROM users WHERE email=?");
    $checkEmail->bind_param("s", $data['email']);
    $checkEmail->execute();
    $checkEmail->store_result();

    if ($checkEmail->num_rows > 0) {
        throw new Exception("Email already exists");
    }

    /* ===============================
       2️⃣ CHECK DUPLICATE ENROLLMENT
    =============================== */

    $checkEnroll = $conn->prepare("SELECT enrollment_no FROM students WHERE enrollment_no=?");
    $checkEnroll->bind_param("s", $data['enrollment']);
    $checkEnroll->execute();
    $checkEnroll->store_result();

    if ($checkEnroll->num_rows > 0) {
        throw new Exception("Enrollment already exists");
    }

    /* ===============================
       3️⃣ INSERT INTO USERS TABLE
    =============================== */

    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

    $stmtUser = $conn->prepare("
        INSERT INTO users (email, password, role, status)
        VALUES (?, ?, 'student', 'approved')
    ");

    $stmtUser->bind_param("ss", $data['email'], $hashedPassword);

    if (!$stmtUser->execute()) {
        throw new Exception($stmtUser->error);
    }

    $user_id = $conn->insert_id;

    /* ===============================
       4️⃣ DERIVE DEPARTMENT FROM CLASS
    =============================== */

    $department_code = substr($data['class'], 0, 2);

    /* ===============================
       5️⃣ DERIVE SEMESTER FROM CLASS
       Example: IF6KA → SEM6
    =============================== */

    preg_match('/\d+/', $data['class'], $match);
    $semester = "SEM" . ($match[0] ?? "1");

    /* ===============================
       6️⃣ INSERT INTO STUDENTS TABLE
    =============================== */

    $stmtStudent = $conn->prepare("
        INSERT INTO students
        (roll_no, user_id, full_name, class,
         mobile_no, parent_mobile_no,
         enrollment_no, department_code,
         current_semester, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'studying')
    ");

    $stmtStudent->bind_param(
        "sisssssss",
        $data['roll'],
        $user_id,
        $data['name'],
        $data['class'],
        $data['phone'],
        $data['parentPhone'],
        $data['enrollment'],
        $department_code,
        $semester
    );

    if (!$stmtStudent->execute()) {
        throw new Exception($stmtStudent->error);
    }

    /* ===============================
       7️⃣ LINK PARENT USING ENROLLMENT
    =============================== */

   $stmtParent = $conn->prepare("
    UPDATE parents
    SET enrollment_no = ?
    WHERE mobile_no = ?
");

$stmtParent->bind_param("ss", $data['enrollment'], $data['parentPhone']);
$stmtParent->execute();


    /* ===============================
       COMMIT
    =============================== */

    $conn->commit();

    echo json_encode([
        "status" => true,
        "message" => "Student added successfully"
    ]);

} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);
}