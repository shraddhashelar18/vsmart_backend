<?php
require_once("../../config.php");
require_once("../../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$action = $data['action'] ?? '';

/* ===============================
1️⃣ GET DEPARTMENTS
=============================== */

if ($action == "departments") {

    $res = $conn->query("SELECT DISTINCT department_code FROM departments");

    $list = [];

    while ($row = $res->fetch_assoc()) {
        $list[] = $row['department_code'];
    }

    echo json_encode([
        "status" => true,
        "departments" => $list
    ]);
    exit;
}

/* ===============================
2️⃣ GET CLASSES (BY DEPARTMENT)
=============================== */

if ($action == "classes") {

    $dept = $data['department'];

    // get active semester
    $settings = $conn->query("SELECT active_semester FROM settings WHERE id=1")->fetch_assoc();
    $cycle = $settings['active_semester']; // EVEN or ODD

    if ($cycle == "EVEN") {
        $semFilter = "current_semester IN ('SEM2','SEM4','SEM6')";
    } else {
        $semFilter = "current_semester IN ('SEM1','SEM3','SEM5')";
    }

    $stmt = $conn->prepare("
        SELECT DISTINCT class 
        FROM students 
        WHERE department_code=? AND $semFilter
    ");

    $stmt->bind_param("s", $dept);
    $stmt->execute();

    $res = $stmt->get_result();

    $classes = [];

    while ($row = $res->fetch_assoc()) {
        $classes[] = $row['class'];
    }

    echo json_encode([
        "status" => true,
        "classes" => $classes
    ]);
    exit;
}

/* ===============================
3️⃣ GET ATTENDANCE REPORT
=============================== */

/* ===============================
3️⃣ GET ATTENDANCE REPORT
=============================== */

if ($action == "report") {

    $class = $data['class'];
    $month = $data['month'];

    $stmt = $conn->prepare("
        SELECT
            s.full_name,
            SUM(CASE WHEN a.status='Present' THEN 1 ELSE 0 END) AS present,
            COUNT(a.id) AS total
        FROM students s
        LEFT JOIN attendance a 
            ON s.user_id = a.student_id
            AND MONTH(a.date) = ?
        WHERE s.class = ?
        GROUP BY s.user_id
    ");

    $stmt->bind_param("is", $month, $class);

    $stmt->execute();
    $res = $stmt->get_result();

    $students = [];

    while ($row = $res->fetch_assoc()) {

        $students[] = [
            "name" => $row['full_name'],
            "present" => (int) $row['present'],
            "total" => (int) $row['total']
        ];
    }

    echo json_encode([
        "status" => true,
        "students" => $students
    ]);
    exit;
}

echo json_encode(["status" => false, "message" => "Invalid action"]);