<?php
require_once("../config.php");
require_once("../api_guard.php");
require_once("../promotion_helper.php");
require_once("../cors.php");
header("Content-Type: application/json");

/* ================= ROLE CHECK ================= */

if ($currentRole != 'hod' && $currentRole != 'principal') {
    echo json_encode([
        "status" => false,
        "message" => "Access Denied"
    ]);
    exit;
}

/* ================= INPUT ================= */

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['class']) || empty(trim($data['class']))) {
    echo json_encode([
        "status" => false,
        "message" => "Class is required"
    ]);
    exit;
}

$class = trim($data['class']);

/* ================= CLASS VALIDATION ================= */

if (!preg_match('/^[A-Z]{2}[0-9][A-Z]{2}$/', $class)) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid class format"
    ]);
    exit;
}

/* ================= ATKT LIMIT ================= */

$setting = $conn->query("SELECT atkt_limit FROM settings LIMIT 1");

if (!$setting) {
    echo json_encode([
        "status" => false,
        "message" => "Failed to load settings"
    ]);
    exit;
}

$atktLimit = (int)$setting->fetch_assoc()['atkt_limit'];

/* ================= GET STUDENTS ================= */

$stmt = $conn->prepare("
    SELECT user_id, full_name, class, current_semester, status
    FROM students
    WHERE class = ?
");

$stmt->bind_param("s", $class);
$stmt->execute();
$result = $stmt->get_result();

$students = [];

/* ================= PROMOTION PROCESS ================= */

while ($row = $result->fetch_assoc()) {

    $promotion = calculatePromotion($conn, $row['user_id'], $atktLimit);

    $currentClass = $row['class'];

    /* Extract semester number */
    $currentSemester = (int) preg_replace('/[^0-9]/', '', $row['current_semester']);

    $newSemester = $currentSemester;
    $newClass = $currentClass;

    /* ===== PROMOTION LOGIC ===== */

    if (
        $promotion['status'] == "PROMOTED" ||
        $promotion['status'] == "PROMOTED_WITH_ATKT"
    ) {

        if ($currentSemester < 6) {

            $newSemester = $currentSemester + 1;

            $department = substr($currentClass, 0, 2);
            $division = substr($currentClass, -2);

            $newClass = $department . $newSemester . $division;

        } else {

            /* SEM6 completed */

            if ($promotion['status'] == "PROMOTED") {
                $promotion['status'] = "COMPLETED";
            }

            $newSemester = 6;
            $newClass = $currentClass;
        }
    }

    $newSemesterStr = "SEM" . $newSemester;

    /* ===== UPDATE STUDENT ===== */

    $update = $conn->prepare("
        UPDATE students
        SET status = ?, current_semester = ?, class = ?
        WHERE user_id = ?
    ");

    $update->bind_param(
        "sssi",
        $promotion['status'],
        $newSemesterStr,
        $newClass,
        $row['user_id']
    );

    $update->execute();

    /* ===== RESPONSE DATA ===== */

    $students[] = [
        "student_id" => $row['user_id'],
        "name" => $row['full_name'],
        "oldClass" => $currentClass,
        "newClass" => $newClass,
        "oldSemester" => $currentSemester,
        "newSemester" => $newSemester,
        "promotionStatus" => $promotion['status'],
        "percentage" => $promotion['percentage'],
        "backlogCount" => $promotion['backlogCount'],
        "ktSubjects" => $promotion['ktSubjects']
    ];
}

/* ================= RESPONSE ================= */

echo json_encode([
    "status" => true,
    "class" => $class,
    "students" => $students
]);
?>