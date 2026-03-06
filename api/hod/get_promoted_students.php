<?php
require_once("../config.php");
require_once("../api_guard.php");
require_once("../promotion_helper.php");

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
    SELECT user_id, full_name, current_semester, class, status
    FROM students
    WHERE class = ?
");

$stmt->bind_param("s", $class);
$stmt->execute();
$result = $stmt->get_result();

$students = [];

/* ================= PROMOTION CALCULATION ================= */

while ($row = $result->fetch_assoc()) {

    $promotion = calculatePromotion($conn, $row['user_id'], $atktLimit);

    $currentSemester = (int) preg_replace('/[^0-9]/', '', $row['current_semester']);
    $currentClass = $row['class'];

    $newSemester = $currentSemester;
    $newClass = $currentClass;

    /* ===== PROMOTION LOGIC ===== */

    if ($promotion['status'] == "PROMOTED" || $promotion['status'] == "PROMOTED_WITH_ATKT") {

        $newSemester = $currentSemester + 1;

        // Extract department and division
        $department = substr($currentClass, 0, 2);
        $division = substr($currentClass, -2);

        $newClass = $department . $newSemester . $division;
    }

    /* ===== UPDATE STUDENT RECORD ===== */

    $update = $conn->prepare("
        UPDATE students
        SET status = ?, current_semester = ?, class = ?
        WHERE user_id = ?
    ");

   $newSemesterStr = "SEM" . $newSemester;

    $update->bind_param(
        "sssi",
        $promotion['status'],
        $newSemesterStr,
        $newClass,
        $row['user_id']
    );

    $update->execute();

    /* ===== RETURN DATA ===== */

    $students[] = [
        "name" => $row['full_name'],
        "oldClass" => $currentClass,
        "newClass" => $newClass,
        "oldSemester" => $currentSemester,
        "newSemester" => $newSemester,
        "backlogCount" => $promotion['backlogCount'],
        "promotionStatus" => $promotion['status'],
        "percentage" => $promotion['percentage'],
        "ktSubjects" => $promotion['ktSubjects']
    ];
}

/* ================= RESPONSE ================= */

echo json_encode([
    "status" => true,
    "students" => $students
]);
?>