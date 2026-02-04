<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

// Allow POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "status" => false,
        "message" => "Method not allowed"
    ]);
    exit;
}

/* ---------- COMMON INPUT ---------- */
$full_name = trim($_POST['full_name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$password  = $_POST['password'] ?? '';
$role      = $_POST['role'] ?? '';

if ($full_name === '' || $email === '' || $password === '' || $role === '') {
    echo json_encode([
        "status" => false,
        "message" => "All fields are required"
    ]);
    exit;
}

/* ---------- ALLOWED ROLES ---------- */
$allowed_roles = ['student', 'teacher', 'parent'];
if (!in_array($role, $allowed_roles)) {
    echo json_encode([
        "status" => false,
        "message" => "Unauthorized role"
    ]);
    exit;
}

/* ---------- EMAIL CHECK ---------- */
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode([
        "status" => false,
        "message" => "Email already registered"
    ]);
    exit;
}

/* ---------- CREATE USER ---------- */
$hashed = password_hash($password, PASSWORD_BCRYPT);

$stmt = $conn->prepare(
    "INSERT INTO users (email, password, role, status)
     VALUES (?, ?, ?, 'pending')"
);
$stmt->bind_param("sss", $email, $hashed, $role);
$stmt->execute();

$user_id = $stmt->insert_id;

/* ---------- ROLE-SPECIFIC ---------- */

// STUDENT
if ($role === "student") {

    $roll_no = $_POST['roll_no'] ?? '';
    $class   = $_POST['class'] ?? '';
    $mobile  = $_POST['mobile_no'] ?? '';
    $pmobile = $_POST['parent_mobile_no'] ?? '';

    if ($roll_no === '' || $class === '' || $mobile === '' || $pmobile === '') {
        echo json_encode([
            "status" => false,
            "message" => "All student fields are required"
        ]);
        exit;
    }

    $stmt = $conn->prepare(
        "INSERT INTO students
         (roll_no, user_id, full_name, class, mobile_no, parent_mobile_no)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "sissss",
        $roll_no, $user_id, $full_name, $class, $mobile, $pmobile
    );
    $stmt->execute();
}

// TEACHER
elseif ($role === "teacher") {

    $emp_id = $_POST['employee_id'] ?? '';
    $mobile = $_POST['mobile_no'] ?? '';

    if ($emp_id === '' || $mobile === '') {
        echo json_encode([
            "status" => false,
            "message" => "All teacher fields are required"
        ]);
        exit;
    }

    $stmt = $conn->prepare(
        "INSERT INTO teachers
         (employee_id, user_id, full_name, mobile_no)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "siss",
        $emp_id, $user_id, $full_name, $mobile
    );
    $stmt->execute();
}

// PARENT
elseif ($role === "parent") {

    $enroll = $_POST['enrollment_no'] ?? '';
    $mobile = $_POST['mobile_no'] ?? '';

    if ($enroll === '' || $mobile === '') {
        echo json_encode([
            "status" => false,
            "message" => "All parent fields are required"
        ]);
        exit;
    }

    $stmt = $conn->prepare(
        "INSERT INTO parents
         (enrollment_no, user_id, full_name, mobile_no)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "siss",
        $enroll, $user_id, $full_name, $mobile
    );
    $stmt->execute();
}

/* ---------- SUCCESS ---------- */
echo json_encode([
    "status" => true,
    "message" => ucfirst($role) . " registered successfully. Waiting for admin approval"
]);
