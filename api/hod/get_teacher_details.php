<?php
require_once("../config.php");
require_once("../api_guard.php");
require_once("../cors.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

/* ==============================
   1️⃣ Validate Input
============================== */

if (!isset($data['user_id']) || empty($data['user_id'])) {
    echo json_encode([
        "status" => false,
        "message" => "Teacher ID is required"
    ]);
    exit;
}

if (!is_numeric($data['user_id'])) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid Teacher ID"
    ]);
    exit;
}

$user_id = (int)$data['user_id'];

/* ==============================
   2️⃣ Get Teacher Basic Details
============================== */

$stmt = $conn->prepare("
    SELECT 
        t.user_id,
        t.full_name,
        t.mobile_no,
        u.email
    FROM teachers t
    JOIN users u ON t.user_id = u.user_id
    WHERE t.user_id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode([
        "status" => false,
        "message" => "Teacher not found"
    ]);
    exit;
}

$teacher = $result->fetch_assoc();

/* ==============================
   3️⃣ Get Teaching Assignments
============================== */

$assignStmt = $conn->prepare("
    SELECT class, subject, department
    FROM teacher_assignments
    WHERE user_id = ? AND status = 'active'
");

$assignStmt->bind_param("i", $user_id);
$assignStmt->execute();
$assignResult = $assignStmt->get_result();

$assignments = [];
$department = null;

while ($row = $assignResult->fetch_assoc()) {

    $assignments[] = [
        "className" => $row['class'],
        "subject" => $row['subject']
    ];

    if ($department === null) {
        $department = $row['department'];
    }
}

/* ==============================
   4️⃣ Check Class Teacher
============================== */

$classTeacherStmt = $conn->prepare("
    SELECT class_name
    FROM classes
    WHERE class_teacher = ?
");

$classTeacherStmt->bind_param("i", $user_id);
$classTeacherStmt->execute();
$classResult = $classTeacherStmt->get_result();

$isClassTeacher = false;
$classTeacherOf = null;

if ($classResult->num_rows > 0) {
    $row = $classResult->fetch_assoc();
    $isClassTeacher = true;
    $classTeacherOf = $row['class_name'];
}

/* ==============================
   5️⃣ Final JSON Response
============================== */

echo json_encode([
    "status" => true,
    "teacher" => [
        "id" => (string)$teacher['user_id'],
        "name" => $teacher['full_name'],
        "email" => $teacher['email'],
        "mobile" => $teacher['mobile_no'],
        "department" => $department ?? "",
        "isClassTeacher" => $isClassTeacher,
        "classTeacherOf" => $classTeacherOf,
        "assignments" => $assignments
    ]
]);
?>