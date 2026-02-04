<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

$conn->begin_transaction();

try {

    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $mobile    = trim($_POST['mobile_no'] ?? '');
    $class     = trim($_POST['class'] ?? '');
    $roll_no   = trim($_POST['roll_no'] ?? '');
    $parent    = trim($_POST['parent_mobile_no'] ?? '');

    if ($full_name==='' || $email==='' || $mobile==='' || $class==='' || $roll_no==='') {
        throw new Exception("All required fields are mandatory");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email");
    }

    // check email
    $check = $conn->prepare("SELECT user_id FROM users WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        throw new Exception("Email already exists");
    }

    // check roll no
    $check = $conn->prepare("SELECT roll_no FROM students WHERE roll_no=?");
    $check->bind_param("s", $roll_no);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        throw new Exception("Roll number already exists");
    }

    // insert into users (NO PASSWORD)
    $stmt = $conn->prepare(
        "INSERT INTO users (email, role, status, first_login)
         VALUES (?, 'student', 'approved', 1)"
    );
    $stmt->bind_param("s", $email);

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $user_id = $stmt->insert_id;

    // insert into students
    $stmt = $conn->prepare(
        "INSERT INTO students
        (roll_no, user_id, full_name, class, mobile_no, parent_mobile_no)
        VALUES (?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "sissss",
        $roll_no,
        $user_id,
        $full_name,
        $class,
        $mobile,
        $parent
    );

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $conn->commit();

    echo json_encode([
        "status" => true,
        "message" => "Student added successfully. Student will set password on first login"
    ]);

} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);
}