<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status"=>false,"message"=>"Method not allowed"]);
    exit;
}

/* ---------- COMMON INPUT ---------- */
$full_name = trim($_POST['full_name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$password  = $_POST['password'] ?? '';
$role      = $_POST['role'] ?? '';

if ($full_name === '' || $email === '' || $password === '' || $role === '') {
    echo json_encode(["status"=>false,"message"=>"All fields are required"]);
    exit;
}

$allowed_roles = ['student','teacher','parent'];
if (!in_array($role, $allowed_roles)) {
    echo json_encode(["status"=>false,"message"=>"Unauthorized role"]);
    exit;
}

/* ---------- ROLE-SPECIFIC VALIDATION FIRST ---------- */
if ($role === "student") {
    if (
        empty($_POST['roll_no']) ||
        empty($_POST['class']) ||
        empty($_POST['mobile_no']) ||
        empty($_POST['parent_mobile_no'])
    ) {
        echo json_encode(["status"=>false,"message"=>"All student fields are required"]);
        exit;
    }
}

if ($role === "teacher") {
    if (
        empty($_POST['employee_id']) ||
        empty($_POST['mobile_no'])
    ) {
        echo json_encode(["status"=>false,"message"=>"All teacher fields are required"]);
        exit;
    }
}

if ($role === "parent") {
    if (
        empty($_POST['enrollment_no']) ||
        empty($_POST['mobile_no'])
    ) {
        echo json_encode(["status"=>false,"message"=>"All parent fields are required"]);
        exit;
    }
}

/* ---------- EMAIL CHECK ---------- */
$check = $conn->prepare("SELECT user_id FROM users WHERE email=?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(["status"=>false,"message"=>"Email already registered"]);
    exit;
}

/* ---------- START TRANSACTION ---------- */
$conn->begin_transaction();

try {

    /* ---------- CREATE USER ---------- */
    $hashed = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare(
        "INSERT INTO users (email,password,role,status)
         VALUES (?,?,?,'pending')"
    );
    $stmt->bind_param("sss", $email, $hashed, $role);
    $stmt->execute();

    $user_id = $stmt->insert_id;

    /* ---------- ROLE TABLE INSERT ---------- */

    if ($role === "student") {
        $stmt = $conn->prepare(
            "INSERT INTO students
            (roll_no,user_id,full_name,class,mobile_no,parent_mobile_no)
            VALUES (?,?,?,?,?,?)"
        );
        $stmt->bind_param(
            "sissss",
            $_POST['roll_no'],
            $user_id,
            $full_name,
            $_POST['class'],
            $_POST['mobile_no'],
            $_POST['parent_mobile_no']
        );
        $stmt->execute();
    }

    if ($role === "teacher") {
        $stmt = $conn->prepare(
            "INSERT INTO teachers
            (employee_id,user_id,full_name,mobile_no)
            VALUES (?,?,?,?)"
        );
        $stmt->bind_param(
            "siss",
            $_POST['employee_id'],
            $user_id,
            $full_name,
            $_POST['mobile_no']
        );
        $stmt->execute();
    }

    if ($role === "parent") {
        $stmt = $conn->prepare(
            "INSERT INTO parents
            (enrollment_no,user_id,full_name,mobile_no)
            VALUES (?,?,?,?)"
        );
        $stmt->bind_param(
            "siss",
            $_POST['enrollment_no'],
            $user_id,
            $full_name,
            $_POST['mobile_no']
        );
        $stmt->execute();
    }

    /* ---------- COMMIT ---------- */
    $conn->commit();

    echo json_encode([
        "status"=>true,
        "message"=>ucfirst($role)." registered successfully. Waiting for admin approval"
    ]);

} catch (Exception $e) {

    /* ---------- ROLLBACK ---------- */
    $conn->rollback();

    echo json_encode([
        "status"=>false,
        "message"=>"Registration failed",
        "error"=>$e->getMessage()
    ]);
}