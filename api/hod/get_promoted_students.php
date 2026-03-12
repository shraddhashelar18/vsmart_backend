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

/* ================= PROMOTION PREVIEW ================= */

while ($row = $result->fetch_assoc()) {

    $studentId = $row['user_id'];

    $promotion = calculatePromotion($conn, $studentId, $atktLimit);

    /* Only show promoted or passed out students */

    if (
        $promotion['status'] != "PROMOTED" &&
        strtolower($row['status']) != "passed_out"
    ) {
        continue;
    }

    $currentClass = $row['class'];

    $currentSemester = (int)preg_replace('/[^0-9]/', '', $row['current_semester']);

    $department = substr($currentClass, 0, 2);
    $division = substr($currentClass, -2);

    $oldSemester = $currentSemester - 1;
    $oldClass = $department . $oldSemester . $division;

    $newSemester = $currentSemester;
    $newClass = $currentClass;

    /* Promotion preview */

    if ($promotion['status'] == "PROMOTED" && $currentSemester < 6) {

        $newSemester = $currentSemester + 1;

        $newClass = $department . $newSemester . $division;
    }

    /* PASSED OUT LOGIC */

    $displayStatus = ($row['status'] == "passed_out")
        ? "PASSED_OUT"
        : $promotion['status'];

    /* Build response */

    $students[] = [
        "student_id" => $studentId,
        "name" => $row['full_name'],
        "oldClass" => $oldClass,
        "newClass" => $currentClass,
        "oldSemester" => $oldSemester,
        "newSemester" => $newSemester,
        "promotionStatus" => $displayStatus,
        "percentage" => $promotion['percentage'] ?? null,
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