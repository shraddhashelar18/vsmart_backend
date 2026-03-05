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
   2️⃣ DEPARTMENT LOGIC
================================ */

$data = json_decode(file_get_contents("php://input"), true);

if ($currentRole == 'hod') {
    $department = $currentDepartment;  // Secure
} else {
    if (!isset($data['department'])) {
        echo json_encode([
            "status" => false,
            "message" => "Department required"
        ]);
        exit;
    }
    $department = $data['department'];
}

/* =========================================
   3️⃣ Get Teachers From Department
========================================= */

$stmt = $conn->prepare("
    SELECT DISTINCT 
        t.user_id,
        t.full_name
    FROM teachers t
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
       4️⃣ Check If Class Teacher
    ========================================= */

    $assignStmt = $conn->prepare("
        SELECT class
        FROM teacher_assignments
        WHERE user_id = ?
        AND status = 'active'
    ");

    $assignStmt->bind_param("i", $user_id);
    $assignStmt->execute();
    $assignResult = $assignStmt->get_result();

    $classes = [];

    while ($a = $assignResult->fetch_assoc()) {
        $classes[] = $a['class'];
    }

    $uniqueClasses = array_unique($classes);

    $isClassTeacher = false;
    $classTeacherOf = null;

    if (count($uniqueClasses) == 1 && count($classes) > 0) {
        $isClassTeacher = true;
        $classTeacherOf = array_values($uniqueClasses)[0];
    }

    /* =========================================
       5️⃣ Build Response
    ========================================= */

    $teachers[] = [
        "id" => (string)$user_id,
        "name" => $row['full_name'],
        "email" => "",              // If needed in detail screen
        "mobile" => "",             // If needed
        "department" => $department,
        "isClassTeacher" => $isClassTeacher,
        "classTeacherOf" => $classTeacherOf,
        "assignments" => []         // You can expand later
    ];
}

/* =========================================
   6️⃣ Final JSON
========================================= */

echo json_encode([
    "status" => true,
    "teachers" => $teachers
]);