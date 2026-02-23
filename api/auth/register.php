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

/* ---------- ROLE-SPECIFIC VALIDATION ---------- */
if ($role === "student") {

    $roll_no           = trim($_POST['roll_no'] ?? '');
    $class             = trim($_POST['class'] ?? '');
    $department        = trim($_POST['department'] ?? '');
    $mobile_no         = trim($_POST['mobile_no'] ?? '');
    $parent_mobile_no  = trim($_POST['parent_mobile_no'] ?? '');

    if (
        $roll_no === '' ||
        $class === '' ||
        $department === '' ||
        $mobile_no === '' ||
        $parent_mobile_no === ''
    ) {
        echo json_encode(["status"=>false,"message"=>"All student fields are required"]);
        exit;
    }
}

if ($role === "teacher") {

    $employee_id = trim($_POST['employee_id'] ?? '');
    $mobile_no   = trim($_POST['mobile_no'] ?? '');

    if ($employee_id === '' || $mobile_no === '') {
        echo json_encode(["status"=>false,"message"=>"All teacher fields are required"]);
        exit;
    }
}

if ($role === "parent") {

    $enrollment_no = trim($_POST['enrollment_no'] ?? '');
    $mobile_no     = trim($_POST['mobile_no'] ?? '');

    if ($enrollment_no === '' || $mobile_no === '') {
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

    /* ---------- INSERT ROLE DATA ---------- */

    if ($role === "student") {

        $stmt = $conn->prepare(
            "INSERT INTO students
            (roll_no,user_id,full_name,class,mobile_no,parent_mobile_no,department)
            VALUES (?,?,?,?,?,?,?)"
        );

        $stmt->bind_param(
            "sisssss",
            $roll_no,
            $user_id,
            $full_name,
            $class,
            $mobile_no,
            $parent_mobile_no,
            $department
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
            $employee_id,
            $user_id,
            $full_name,
            $mobile_no
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
            $enrollment_no,
            $user_id,
            $full_name,
            $mobile_no
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

    $conn->rollback();

    echo json_encode([
        "status"=>false,
        "message"=>"Registration failed",
        "error"=>$e->getMessage()
    ]);
}
