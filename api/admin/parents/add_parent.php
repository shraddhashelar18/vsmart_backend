<?php
require_once("../../config.php");
require_once("../../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$name     = $data['name'] ?? '';
$email    = $data['email'] ?? '';
$phone    = $data['phone'] ?? '';
$children = $data['children'] ?? [];  // ARRAY

/* ================= VALIDATION ================= */

if (empty($name) || empty($email) || empty($phone) || empty($children)) {
    echo json_encode([
        "status" => false,
        "message" => "All fields required"
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid email format"
    ]);
    exit;
}

/* CHECK DUPLICATE EMAIL */

$checkEmail = $conn->prepare("SELECT user_id FROM users WHERE email=?");
$checkEmail->bind_param("s", $email);
$checkEmail->execute();
$checkEmail->store_result();

if ($checkEmail->num_rows > 0) {
    echo json_encode([
        "status" => false,
        "message" => "Email already exists"
    ]);
    exit;
}

/* GENERATE TEMP PASSWORD */
$tempPassword = rand(100000, 999999);
$hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);

/* START TRANSACTION */
$conn->begin_transaction();

try {

    /* INSERT INTO USERS */
    $stmtUser = $conn->prepare("
        INSERT INTO users (email, password, role, status)
        VALUES (?, ?, 'parent', 'approved')
    ");

    $stmtUser->bind_param("ss", $email, $hashedPassword);
    $stmtUser->execute();

    $user_id = $stmtUser->insert_id;

    /* LOOP FOR EACH CHILD */
    foreach ($children as $enrollment) {

        /* CHECK STUDENT EXISTS */
        $checkStudent = $conn->prepare("
            SELECT enrollment_no FROM students WHERE enrollment_no=?
        ");
        $checkStudent->bind_param("s", $enrollment);
        $checkStudent->execute();

        if ($checkStudent->get_result()->num_rows == 0) {
            throw new Exception("Student not found: $enrollment");
        }

        /* INSERT INTO PARENTS TABLE */
        $stmtParent = $conn->prepare("
            INSERT INTO parents (enrollment_no, user_id, full_name, mobile_no)
            VALUES (?, ?, ?, ?)
        ");
        $stmtParent->bind_param("siss", $enrollment, $user_id, $name, $phone);
        $stmtParent->execute();

        /* UPDATE STUDENT TABLE */
        $stmtStudent = $conn->prepare("
            UPDATE students
            SET parent_mobile_no = ?
            WHERE enrollment_no = ?
        ");
        $stmtStudent->bind_param("ss", $phone, $enrollment);
        $stmtStudent->execute();
    }

    $conn->commit();

    echo json_encode([
        "status" => true,
        "message" => "Parent added successfully",
        "temporaryPassword" => $tempPassword
    ]);

} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);
}
?>