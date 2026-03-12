<?php
require_once("../../config.php");
require_once("../../api_guard.php");
require_once("../../cors.php");

header("Content-Type: application/json");

$action = $_GET['action'] ?? '';

/* ---------------------------------------
1️⃣ GET CLASSES BY DEPARTMENT
--------------------------------------- */
if ($action == "get_classes") {

    $department = $_GET['department'] ?? '';

    if (empty($department)) {
        echo json_encode([
            "status" => false,
            "message" => "Department required"
        ]);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT class_name
        FROM classes
        WHERE department=?
        ORDER BY semester ASC, class_name ASC
    ");

    $stmt->bind_param("s", $department);
    $stmt->execute();

    $res = $stmt->get_result();

    $classes = [];

    while ($row = $res->fetch_assoc()) {
        $classes[] = $row["class_name"];
    }

    echo json_encode([
        "status" => true,
        "classes" => $classes
    ]);
    exit;
}

/* ---------------------------------------
2️⃣ GET SUBJECTS BY CLASS
--------------------------------------- */
if ($action == "get_subjects") {

    $class = $_GET['class'] ?? '';

    if (empty($class)) {
        echo json_encode([
            "status" => false,
            "message" => "Class required"
        ]);
        exit;
    }

    $prefix = substr($class, 0, 4);

    $stmt = $conn->prepare("
        SELECT subject_name
        FROM semester_subjects
        WHERE class LIKE CONCAT(?, '%')
    ");

    $stmt->bind_param("s", $prefix);
    $stmt->execute();

    $res = $stmt->get_result();

    $subjects = [];

    while ($row = $res->fetch_assoc()) {
        $subjects[] = $row["subject_name"];
    }

    echo json_encode([
        "status" => true,
        "subjects" => $subjects
    ]);
    exit;
}

/* ---------------------------------------
3️⃣ GET ALLOCATED SUBJECTS
--------------------------------------- */
if ($action == "get_allocated") {

    $class = $_GET['class'] ?? '';

    if (empty($class)) {
        echo json_encode([
            "status" => false,
            "message" => "Class required"
        ]);
        exit;
    }

    $prefix = substr($class, 0, 4);

    $stmt = $conn->prepare("
        SELECT DISTINCT subject
        FROM teacher_assignments
        WHERE class LIKE CONCAT(?, '%')
    ");

    $stmt->bind_param("s", $prefix);
    $stmt->execute();

    $res = $stmt->get_result();

    $subjects = [];

    while ($row = $res->fetch_assoc()) {
        $subjects[] = $row["subject"];
    }

    echo json_encode([
        "status" => true,
        "allocated_subjects" => $subjects
    ]);
    exit;
}
if ($action == "assign_teacher") {

    if ($currentRole != "admin") {
        echo json_encode([
            "status" => false,
            "message" => "Access denied"
        ]);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"), true);

    $user_id = $data['user_id'] ?? '';
    $department = $data['department'] ?? '';
    $class = $data['class'] ?? '';
    $subjects = $data['subjects'] ?? [];

    if (empty($user_id) || empty($department) || empty($class) || empty($subjects)) {
        echo json_encode([
            "status" => false,
            "message" => "All fields required"
        ]);
        exit;
    }

    $conn->begin_transaction();

    try {

        /* 1. Make sure teacher record exists */
        $check = $conn->prepare("SELECT user_id FROM teachers WHERE user_id=?");
        $check->bind_param("i", $user_id);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows == 0) {
            throw new Exception("Teacher profile not found. Registration data missing.");
        }

        /* 2. Insert assignments */
        foreach ($subjects as $subject) {

            // prevent duplicate assignment
            $dup = $conn->prepare("
                SELECT id
                FROM teacher_assignments
                WHERE class=? AND subject=?
            ");
            $dup->bind_param("ss", $class, $subject);
            $dup->execute();
            $dupRes = $dup->get_result();

            if ($dupRes->num_rows > 0) {
                continue;
            }

            $stmt = $conn->prepare("
                INSERT INTO teacher_assignments
                (user_id, department, class, subject, status)
                VALUES (?,?,?,?, 'active')
            ");

            $stmt->bind_param("isss", $user_id, $department, $class, $subject);
            $stmt->execute();
        }

        /* 3. Approve user */
        $update = $conn->prepare("
            UPDATE users
            SET status='approved'
            WHERE user_id=?
        ");
        $update->bind_param("i", $user_id);
        $update->execute();

        $conn->commit();

        echo json_encode([
            "status" => true,
            "message" => "Teacher assigned and approved successfully"
        ]);
        exit;

    } catch (Exception $e) {
        $conn->rollback();

        echo json_encode([
            "status" => false,
            "message" => $e->getMessage()
        ]);
        exit;
    }
}
/* ---------------------------------------
INVALID ACTION
--------------------------------------- */
echo json_encode([
    "status" => false,
    "message" => "Invalid action"
]);
exit;