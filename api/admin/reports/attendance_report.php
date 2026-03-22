<?php
require_once("../../config.php");
require_once("../../api_guard.php");
require_once("../../cors.php");

header("Content-Type: application/json");
if($currentRole != "admin"){
    echo json_encode(["status"=>false,"message"=>"Access denied"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$action = $data['action'] ?? '';

/* ===================================================
1️⃣ GET DEPARTMENTS
=================================================== */

if ($action == "departments") {

    $res = $conn->query("SELECT DISTINCT department FROM classes");

    $departments = [];

    while ($row = $res->fetch_assoc()) {
        $departments[] = $row['department'];
    }

    echo json_encode([
        "status" => true,
        "departments" => $departments
    ]);
    exit;
}

/* ===================================================
2️⃣ GET CLASSES BY DEPARTMENT
=================================================== */
if ($action == "classes") {

    $dept = $data['department'];

    // get active semester
    $settings = $conn->query("SELECT active_semester FROM settings WHERE id=1")->fetch_assoc();
    $cycle = $settings['active_semester'];

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

    while ($row = $res->fetch_assoc()) {
        $classes[] = $row['class_name'];
    }

    echo json_encode([
        "status" => true,
        "classes" => $classes
    ]);
    exit;
}

/* ===================================================
3️⃣ GET MONTHS
=================================================== */
if ($action == "months") {

    // get active semester
    $settings = $conn->query("SELECT active_semester FROM settings WHERE id=1")->fetch_assoc();
    $semester = $settings['active_semester'];

    if ($semester == "EVEN") {

        $months = [
            "December",
            "January",
            "February",
            "March",
            "April",
            "May"
        ];

    } else {

        $months = [
            "June",
            "July",
            "August",
            "September",
            "October",
            "November"
        ];

    }

    echo json_encode([
        "status" => true,
        "months" => $months
    ]);
    exit;
}

/* ===================================================
4️⃣ CHECK MONTH ENABLED
=================================================== */

if ($action == "check_month") {

    $month = $data['month']; // month number sent from Flutter
    $currentMonth = date("n");

    // semester month order
    $evenMonths = [12, 1, 2, 3, 4, 5];
    $oddMonths = [6, 7, 8, 9, 10, 11];

    // get active semester
    $settings = $conn->query("SELECT active_semester FROM settings WHERE id=1")->fetch_assoc();
    $semester = $settings['active_semester'];

    $months = ($semester == "EVEN") ? $evenMonths : $oddMonths;

    $currentIndex = array_search($currentMonth, $months);
    $monthIndex = array_search($month, $months);

    $enabled = false;

    if ($monthIndex !== false && $currentIndex !== false) {
        $enabled = $monthIndex < $currentIndex;
    }

    echo json_encode([
        "status" => true,
        "enabled" => $enabled
    ]);
    exit;
}
/* ===================================================
5️⃣ ATTENDANCE REPORT
=================================================== */

if ($action == "report") {

    $class = $data['class'];
    $month = $data['month'];

    $stmt = $conn->prepare("
        SELECT
            s.full_name,
            COUNT(a.id) AS total,
           SUM(CASE WHEN a.status='P' THEN 1 ELSE 0 END) AS present
        FROM students s
        LEFT JOIN attendance a
            ON s.user_id = a.student_id
            AND a.class = ?
            AND MONTH(a.date) = ?
        WHERE s.class = ?
        GROUP BY s.user_id
        ORDER BY s.full_name
    ");

    $stmt->bind_param("sis", $class, $month, $class);
    $stmt->execute();

    $res = $stmt->get_result();

    $students = [];

    while ($row = $res->fetch_assoc()) {

        $present = (int) $row['present'];
        $total = (int) $row['total'];

        $percentage = 0;

        if ($total > 0) {
            $percentage = round(($present / $total) * 100, 2);
        }

        $students[] = [
            "name" => $row['full_name'],
            "present" => $present,
            "total" => $total,
            "percentage" => $percentage
        ];
    }

    echo json_encode([
        "status" => true,
        "students" => $students
    ]);
    exit;
}
/* ===================================================
INVALID ACTION
=================================================== */

echo json_encode([
    "status" => false,
    "message" => "Invalid Action"
]);
