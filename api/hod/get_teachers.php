<?php
require_once("../config.php");
require_once("../api_guard.php");
require_once("../cors.php");

header("Content-Type: application/json");

/* ===============================
1️⃣ ROLE CHECK
================================ */

if ($currentRole != 'hod' && $currentRole != 'principal') {
    echo json_encode([
        "status" => false,
        "message" => "Access Denied"
    ]);
    exit;
}

/* ===============================
2️⃣ GET DEPARTMENT
================================ */

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['department']) || empty($data['department'])) {
    echo json_encode([
        "status" => false,
        "message" => "Department required"
    ]);
    exit;
}

$department = $data['department'];

/* =========================================
3️⃣ GET TEACHERS
========================================= */

$stmt = $conn->prepare("
    SELECT DISTINCT
        t.user_id,
        t.full_name,
        t.mobile_no,
        u.email
    FROM teachers t
    JOIN users u ON t.user_id = u.user_id
    JOIN teacher_assignments ta ON t.user_id = ta.user_id
    WHERE ta.department = ?
    AND ta.status = 'active'
");

$stmt->bind_param("s", $department);
$stmt->execute();
$result = $stmt->get_result();

$teachers = [];

while ($row = $result->fetch_assoc()) {

    $user_id = $row['user_id'];

    /* =========================================
    4️⃣ GET ASSIGNMENTS
    ========================================= */

    $assignStmt = $conn->prepare("
        SELECT class, subject
        FROM teacher_assignments
        WHERE user_id = ?
        AND status = 'active'
    ");

    $assignStmt->bind_param("i", $user_id);
    $assignStmt->execute();
    $assignResult = $assignStmt->get_result();

    $assignments = [];

    while ($a = $assignResult->fetch_assoc()) {

        $assignments[] = [
            "className" => $a['class'],
            "subject" => $a['subject']
        ];
    }

    /* =========================================
    5️⃣ CLASS TEACHER FROM CLASSES TABLE
    ========================================= */

    $classStmt = $conn->prepare("
        SELECT class_name
        FROM classes
        WHERE class_teacher = ?
    ");

    $classStmt->bind_param("i", $user_id);
    $classStmt->execute();
    $classResult = $classStmt->get_result();

    $isClassTeacher = false;
    $classTeacherOf = null;

    if ($classResult->num_rows > 0) {
        $isClassTeacher = true;

        $rowClass = $classResult->fetch_assoc();
        $classTeacherOf = $rowClass['class_name'];
    }

    /* =========================================
    6️⃣ FINAL OBJECT
    ========================================= */

    $teachers[] = [
        "id" => (string)$user_id,
        "name" => $row['full_name'],
        "email" => $row['email'],
        "mobile" => $row['mobile_no'],
        "department" => $department,
        "isClassTeacher" => $isClassTeacher,
        "classTeacherOf" => $classTeacherOf,
        "assignments" => $assignments
    ];
}

/* =========================================
7️⃣ RESPONSE
========================================= */

echo json_encode([
    "status" => true,
    "teachers" => $teachers
]);
?>