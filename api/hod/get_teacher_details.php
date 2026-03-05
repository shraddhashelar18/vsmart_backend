<?php
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

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

$user_id = $data['user_id'];

/* ==============================
   1️⃣ Get Teacher Basic Details
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
   2️⃣ Get Assignments
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
        "className" => $row['class'],   // MUST match Flutter
        "subject" => $row['subject']    // MUST match Flutter
    ];

    if ($department === null) {
        $department = $row['department'];
    }
}

/* ==============================
   3️⃣ Class Teacher Logic
   (Temporary simple logic)
   ============================== */

$isClassTeacher = false;
$classTeacherOf = null;

/*
If teacher is assigned to exactly one class
you can treat as class teacher.
(You can improve this later with proper column)
*/

$uniqueClasses = array_unique(array_column($assignments, 'className'));

if (count($uniqueClasses) == 1 && count($assignments) > 0) {
    $isClassTeacher = true;
    $classTeacherOf = $uniqueClasses[0];
}

/* ==============================
   4️⃣ Final JSON Response
   ============================== */

echo json_encode([
    "status" => true,
    "teacher" => [
        "id" => (string)$teacher['user_id'],         // Flutter expects id
        "name" => $teacher['full_name'],             // Flutter expects name
        "email" => $teacher['email'],                // Flutter expects email
        "mobile" => $teacher['mobile_no'],           // Flutter expects mobile
        "department" => $department ?? "",           // Required field
        "isClassTeacher" => $isClassTeacher,         // bool
        "classTeacherOf" => $classTeacherOf,         // nullable
        "assignments" => $assignments                // List<TeachingAssignment>
    ]
]);