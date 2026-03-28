<?php
require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../api_guard.php");
require_once(__DIR__ . "/../../cors.php");

header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* =========================== AUTH CHECK =========================== */
if ($currentRole !== "admin") {
    echo json_encode(["status" => false, "message" => "Access denied"]);
    exit;
}

/* =========================== GET INPUT =========================== */
$raw = file_get_contents("php://input");
$data = !empty($raw) ? json_decode($raw, true) : $_POST;

if (!$data) {
    echo json_encode(["status" => false, "message" => "No data received"]);
    exit;
}
function validateInput($data)
{
    $warnings = [];

    /* ================= NAME ================= */
    if (!preg_match("/^[a-zA-Z ]+$/", $data['name'] ?? '')) {
        $warnings[] = "Name should contain only letters";
    }

    /* ================= EMAIL ================= */
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $warnings[] = "Invalid email format";
    }

    /* ================= PASSWORD ================= */
    $password = $data['password'] ?? '';

    if (strlen($password) < 6) {
        $warnings[] = "Password should be at least 6 characters";
    }

    if (
        !preg_match("/[A-Z]/", $password) ||
        !preg_match("/[a-z]/", $password) ||
        !preg_match("/[0-9]/", $password)
    ) {
        $warnings[] = "Weak password (use upper, lower, number)";
    }

    /* ================= PHONE ================= */
    if (!empty($data['phone']) && !preg_match("/^[0-9]{10}$/", $data['phone'])) {
        $warnings[] = "Phone should be 10 digits";
    }

    /* ================= EMPLOYEE ID ================= */
    if (
        !empty($data['employee_id']) &&
        !preg_match("/^[a-zA-Z]{2}[0-9]{4}$/", $data['employee_id'])
    ) {
        $warnings[] = "Employee ID format should be like vp1009";
    }

    return $warnings;
}
/* =========================== SANITIZE =========================== */
$name = trim($data['name']);
$email = trim($data['email']);
$password = $data['password'];
$phone = $data['phone'] ?? null;
$employee_id = $data['employee_id'] ?? null;
$subjects = $data['subjects'] ?? [];

/* =========================== START TRANSACTION =========================== */
$conn->begin_transaction();

try {

    /* ================= CHECK EMAIL ================= */
    $check = $conn->prepare("SELECT user_id FROM users WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        throw new Exception("Email already exists");
    }

    /* ================= INSERT USER ================= */
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmtUser = $conn->prepare("
        INSERT INTO users (email, password, role, status)
        VALUES (?, ?, 'teacher', 'approved')
    ");
    $stmtUser->bind_param("ss", $email, $hashedPassword);
    $stmtUser->execute();

    $user_id = $stmtUser->insert_id;

    /* ================= INSERT TEACHER ================= */
    $stmtTeacher = $conn->prepare("
        INSERT INTO teachers (user_id, employee_id, full_name, mobile_no)
        VALUES (?, ?, ?, ?)
    ");
    $stmtTeacher->bind_param("isss", $user_id, $employee_id, $name, $phone);
    $stmtTeacher->execute();

    /* ================= INSERT ASSIGNMENTS ================= */
    if (!empty($subjects) && is_array($subjects)) {

        $stmtAssign = $conn->prepare("
            INSERT INTO teacher_assignments 
            (user_id, department, class, subject, status)
            VALUES (?, ?, ?, ?, 'active')
        ");

        foreach ($subjects as $department => $classes) {
            foreach ($classes as $className => $subjectList) {

                if (!is_array($subjectList))
                    continue;

                foreach ($subjectList as $subject) {
                    $stmtAssign->bind_param(
                        "isss",
                        $user_id,
                        $department,
                        $className,
                        $subject
                    );
                    $stmtAssign->execute();
                }
            }
        }
    }

    /* ================= COMMIT ================= */
    $conn->commit();

    echo json_encode([
        "status" => true,
        "message" => "Teacher added successfully"
    ]);

} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);
}