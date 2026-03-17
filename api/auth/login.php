<?php
require_once("../config.php");
require_once("../cors.php");

header("Content-Type: application/json");

/* ===============================
   1️⃣ Validate Input
================================ */

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status"=>false,"message"=>"Invalid request"]);
    exit;
}

$email = trim($data['email'] ?? "");
$password = trim($data['password'] ?? "");

if (empty($email)) {
    echo json_encode(["status"=>false,"message"=>"Email is required"]);
    exit;
}

if (empty($password)) {
    echo json_encode(["status"=>false,"message"=>"Password is required"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status"=>false,"message"=>"Invalid email format"]);
    exit;
}

/* ===============================
   2️⃣ Fetch User
================================ */

$stmt = $conn->prepare("
  SELECT u.user_id, u.email, u.role, u.password, u.status
FROM users u
WHERE u.email=?
LIMIT 1
");
$name = null;

$stmt->bind_param("s",$email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    echo json_encode(["status"=>false,"message"=>"User not found"]);
    exit;
}

$user = $res->fetch_assoc();

/* ===============================
   3️⃣ Verify Password
================================ */

if (!password_verify($password,$user['password'])) {
    echo json_encode(["status"=>false,"message"=>"Invalid password"]);
    exit;
}

/* ===============================
   4️⃣ Status Check
================================ */

if ($user['status'] !== "approved") {
    echo json_encode(["status"=>false,"message"=>"Waiting for admin approval"]);
    exit;
}

/* ===============================
   5️⃣ Role Based Extra Data
================================ */

$departments = [];
$className = null;
$semester = null;

if ($user['role'] == "admin") {

    $stmt = $conn->prepare("
        SELECT full_name
        FROM admins
        WHERE user_id=?
    ");

    $stmt->bind_param("i",$user['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $name = $row['full_name'];
    }
}

/* 🔹 TEACHER */
if ($user['role'] == "teacher") {

    // Get departments
    $tStmt = $conn->prepare("
        SELECT DISTINCT department
        FROM teacher_assignments
        WHERE user_id = ?
    ");

    $tStmt->bind_param("i",$user['user_id']);
    $tStmt->execute();
    $tRes = $tStmt->get_result();

    while ($row = $tRes->fetch_assoc()) {
        if (!empty($row['department'])) {
            $departments[] = $row['department'];
        }
    }

    // Get teacher name
    $stmt = $conn->prepare("
        SELECT full_name
        FROM teachers
        WHERE user_id=?
    ");

    $stmt->bind_param("i",$user['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $name = $row['full_name'];
    }
}

/* 🔹 HOD */
if ($user['role'] == "hod") {

    $hStmt = $conn->prepare("
        SELECT full_name, department
        FROM hods
        WHERE user_id = ?
    ");

    $hStmt->bind_param("i",$user['user_id']);
    $hStmt->execute();
    $hRes = $hStmt->get_result();

    if ($hRes->num_rows > 0) {
        $row = $hRes->fetch_assoc();

        $departments[] = $row['department'];
        $name = $row['full_name'];
    }
}

/* 🔹 PRINCIPAL */
if ($user['role'] == "principal") {

    $stmt = $conn->prepare("
        SELECT full_name
        FROM principal
        WHERE user_id=?
    ");

    $stmt->bind_param("i",$user['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $name = $row['full_name'];
    }

    $pStmt = $conn->query("
        SELECT DISTINCT department
        FROM teacher_assignments
        WHERE status='active'
    ");

    while ($row = $pStmt->fetch_assoc()) {
        $departments[] = $row['department'];
    }
}

/* 🔹 STUDENT */
if ($user['role'] == "student") {

    $sStmt = $conn->prepare("
       SELECT full_name,class,current_semester,department
       FROM students
       WHERE user_id = ?
    ");

    $sStmt->bind_param("i", $user['user_id']);
    $sStmt->execute();
    $sRes = $sStmt->get_result();

    if ($sRes->num_rows > 0) {

        $student = $sRes->fetch_assoc();

        $className = $student['class'];
        $name = $student['full_name'] ?? $user['email'];
        $semester = (int) ($student['current_semester'] ?? 0);

        if (!empty($student['department'])) {
            $departments[] = $student['department'];
        }
    }
}

/* 🔹 PARENT */

if ($user['role'] == "parent") {

    $stmt = $conn->prepare("
        SELECT full_name
        FROM parents
        WHERE user_id=?
    ");

    $stmt->bind_param("i",$user['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $name = $row['full_name'];
    }
}

/* ===============================
   6️⃣ Generate Token
================================ */

$token = hash("sha256",$user['user_id'].time().rand());

$update = $conn->prepare("
    UPDATE users
    SET auth_token=?
    WHERE user_id=?
");

$update->bind_param("si",$token,$user['user_id']);
$update->execute();

/* ===============================
   7️⃣ Final Response
================================ */

echo json_encode([
    "status"=>true,
    "token"=>$token,
    "user"=>[
        "user_id"=>(int)$user['user_id'],
        "email"=>$user['email'],
        "name"=>$name ?? $user['email'],
        "role"=>$user['role'],
        "status"=>$user['status'],
        "departments"=>$departments,
        "className"=>$className,
        "semester"=>$semester
    ]
]);