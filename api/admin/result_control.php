<?php
require_once("../config.php");
require_once("../api_guard.php");
require_once("../cors.php");

header("Content-Type: application/json");

if ($currentRole != "admin") {
    echo json_encode(["status" => false, "message" => "Access denied"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$action = $data['action'] ?? '';

/* ===============================
GET CURRENT SETTINGS
================================ */

if ($action == "get_settings") {

    $res = $conn->query("
SELECT allow_marksheet_upload,final_published
FROM settings LIMIT 1
");

    $row = $res->fetch_assoc();

    echo json_encode([
        "status" => true,
        "allow_marksheet_upload" => (int) $row['allow_marksheet_upload'],
        "final_published" => (int) $row['final_published']
    ]);
    exit;

}

/* ===============================
UPDATE SETTINGS
================================ */

if ($action == "update_settings") {

    $upload = (int) $data['allow_upload'];
    $publish = (int) $data['publish_result'];

    $conn->query("
UPDATE settings
SET allow_marksheet_upload=$upload,
final_published=$publish
");

    echo json_encode([
        "status" => true,
        "message" => "Settings updated"
    ]);
    exit;

}

/* ===============================
UPLOAD PROGRESS
================================ */

if ($action == "upload_progress") {

    $res = $conn->query("
SELECT
class,
COUNT(*) total_students,
SUM(marks_uploaded) uploaded
FROM students
GROUP BY class
ORDER BY class
");

    $classes = [];

    while ($row = $res->fetch_assoc()) {

        $classes[] = [
            "class" => $row['class'],
            "uploaded" => $row['uploaded'],
            "total" => $row['total_students']
        ];

    }

    echo json_encode([
        "status" => true,
        "classes" => $classes
    ]);
    exit;

}

/* ===============================
GET CLASSES BY DEPARTMENT
================================ */

if ($action == "classes") {

    $dept = $data['department'];

    $settings = $conn->query("
    SELECT active_semester
    FROM settings
    LIMIT 1
    ");

    $row = $settings->fetch_assoc();
    $cycle = $row['active_semester'];

    if ($cycle == "EVEN") {
        $semFilter = "semester IN (2,4,6)";
    } else {
        $semFilter = "semester IN (1,3,5)";
    }

    $stmt = $conn->prepare("
    SELECT class_name
    FROM classes
    WHERE department=? AND $semFilter
    ");

    $stmt->bind_param("s", $dept);
    $stmt->execute();

    $res = $stmt->get_result();

    $classes = [];

    while ($r = $res->fetch_assoc()) {
        $classes[] = $r['class_name'];
    }

    echo json_encode([
        "status" => true,
        "classes" => $classes
    ]);

    exit;
}


/* ===============================
STUDENT UPLOAD STATUS
================================ */

if ($action == "student_upload_status") {

    $class = $data['class'];

    $stmt = $conn->prepare("
    SELECT
        full_name,
        marks_uploaded
    FROM students
    WHERE class=?
    ORDER BY full_name
    ");

    $stmt->bind_param("s", $class);
    $stmt->execute();

    $res = $stmt->get_result();

    $students = [];

    while ($row = $res->fetch_assoc()) {

        $students[] = [
            "name" => $row['full_name'],
            "uploaded" => (int) $row['marks_uploaded']
        ];
    }

    echo json_encode([
        "status" => true,
        "students" => $students
    ]);

    exit;
}
if (isset($data['action']) && $data['action'] == "student_upload_status") {

    $class = $data['class'];

    $result = $conn->query("
        SELECT name, marks_uploaded
        FROM students
        WHERE class = '$class'
    ");

    $students = [];

    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    echo json_encode([
        "status" => true,
        "students" => $students
    ]);
    exit;
}

/* ===============================
INVALID
================================ */

echo json_encode([
    "status" => false,
    "message" => "Invalid action"
]);