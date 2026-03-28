<?php

ini_set('display_errors', 1);
require_once(__DIR__ . "/../config.php");   // ensure DB
require_once(__DIR__ . "/../api_guard.php"); // auth
require_once(__DIR__ . "/../cors.php");      // frontend calls
require_once(__DIR__ . "/../../vendor/autoload.php"); // pdf
error_reporting(E_ALL);



ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log');

use Smalot\PdfParser\Parser;

header("Content-Type: application/json");

/* ================= ROLE CHECK ================= */

if ($currentRole != "student") {
    echo json_encode([
        "status" => false,
        "message" => "Access denied"
    ]);
    exit;
}

$student_id = $currentUserId;
$stmt = $conn->prepare("SELECT roll_no FROM students WHERE user_id=?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

if (!$row || !isset($row['roll_no'])) {
    echo json_encode([
        "status" => false,
        "message" => "Roll number not found"
    ]);
    exit;
}

$rollNo = $row['roll_no'];

/* ================= FILE CHECK ================= */

if (!isset($_FILES['marksheet'])) {
    echo json_encode([
        "status" => false,
        "message" => "Marksheet required"
    ]);
    exit;
}

$file = $_FILES['marksheet'];

if ($file['error'] !== 0) {
    echo json_encode([
        "status" => false,
        "message" => "File upload failed"
    ]);
    exit;
}

/* ================= VALIDATION ================= */

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if ($ext != "pdf") {
    echo json_encode([
        "status" => false,
        "message" => "Only PDF allowed"
    ]);
    exit;
}

if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode([
        "status" => false,
        "message" => "File too large"
    ]);
    exit;
}

/* ================= SAVE FILE ================= */

/* ================= SAVE FILE ================= */

$uploadDir = "../../uploads/marksheets/";

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

/* STEP 1: Save temp file */
$tempFileName = $rollNo . "_" . time() . ".pdf";
$tempPath = $uploadDir . $tempFileName;

if (!move_uploaded_file($file['tmp_name'], $tempPath)) {
    echo json_encode([
        "status" => false,
        "message" => "Failed to save file"
    ]);
    exit;
}

/* ================= READ PDF ================= */

try {
    ini_set('memory_limit', '512M');
    set_time_limit(60);
    $parser = new Parser();
    $pdf = $parser->parseFile($tempPath);
    $text = $pdf->getText();
    file_put_contents(__DIR__ . "/debug.txt", $text);

} catch (Throwable $e) {

    echo json_encode([
        "status" => false,
        "message" => "Unable to read marksheet"
    ]);
    exit;
}

if (empty($text)) {
    echo json_encode([
        "status" => false,
        "message" => "Marksheet text not readable"
    ]);
    exit;
}

/* ================= DETECT SEMESTER ================= */

preg_match('/(FIRST|SECOND|THIRD|FOURTH|FIFTH|SIXTH)\s+SEMESTER/i', $text, $semMatch);

$semesterMap = [
    "FIRST" => 1,
    "SECOND" => 2,
    "THIRD" => 3,
    "FOURTH" => 4,
    "FIFTH" => 5,
    "SIXTH" => 6
];

if (!isset($semMatch[1])) {
    echo json_encode([
        "status" => false,
        "message" => "Semester not detected"
    ]);
    exit;
}

$semesterNumber = $semesterMap[strtoupper($semMatch[1])];

/* ================= RENAME FILE AFTER SEM DETECT ================= */

$newFileName = $rollNo . "_SEM" . $semesterNumber . ".pdf";
$newPath = "../../uploads/marksheets/" . $newFileName;

// delete old if exists
if (file_exists($newPath)) {
    unlink($newPath);
}

// rename temp → final
if (!rename($tempPath, $newPath)) {
    echo json_encode([
        "status" => false,
        "message" => "Failed to rename file"
    ]);
    exit;
}

// correct path for DB
$fileName = "uploads/marksheets/" . $newFileName;
/* ================= RENAME FILE AFTER SEM DETECT ================= */




/* ================= CHECK DUPLICATE ================= */

$stmt = $conn->prepare("
SELECT id FROM semester_results 
WHERE student_id=? AND semester=?
");

$stmt->bind_param("ii", $student_id, $semesterNumber);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {

    echo json_encode([
        "status" => false,
        "message" => "Marksheet already uploaded"
    ]);
    exit;
}

/* ================= DELETE OLD MARKS ================= */

$stmt = $conn->prepare("
DELETE FROM marks
WHERE student_id=? AND semester=? AND exam_type='FINAL'
");

$stmt->bind_param("ii", $student_id, $semesterNumber);
$stmt->execute();

/* ================= GET SUBJECTS ================= */

$subjects = [];

$stmt = $conn->prepare("
SELECT subject_name 
FROM semester_subjects
WHERE semester=?
");

$stmt->bind_param("i", $semesterNumber);
$stmt->execute();
$res = $stmt->get_result();

/* ADD THIS HERE */
$subjectList = [];

while ($row = $res->fetch_assoc()) {
    $subjectList[] = $row['subject_name'];
}

/* ================= EXTRACT MARKS ================= */

$subjects = [];

$lines = preg_split("/\r\n|\n|\r/", $text);

for ($i = 0; $i < count($lines); $i++) {

    // merge current + next line (IMPORTANT for BEE)
    $line = $lines[$i];

    // ONLY merge if current line has NO numbers (subject line)
    if (!preg_match('/\d+/', $line) && $i + 1 < count($lines)) {
        $line .= " " . $lines[$i + 1];
    }
    $cleanLine = strtoupper(preg_replace('/\s+/', ' ', str_replace('&', 'AND', $line)));
    foreach ($subjectList as $subject) {

        $cleanSubject = strtoupper(str_replace('&', 'AND', $subject));

        $words = explode(" ", $cleanSubject);

        $match = 0;

        foreach ($words as $word) {
            if (strlen($word) > 4 && strpos($cleanLine, $word) !== false) {
                $match++;
            }
        }

        if ($match >= 1) { // at least 2 words match

            preg_match_all('/\d+/', $line, $nums);

            if (!empty($nums[0]) && count($nums[0]) >= 6) {
                $marks = intval($nums[0][5]);
                $subjects[$subject] = $marks;
            }

            break;
        }
    }
}


/* ================= SAVE MARKS ================= */
// fetch class FIRST
$classStmt = $conn->prepare("SELECT class FROM students WHERE user_id=?");
$classStmt->bind_param("i", $student_id);
$classStmt->execute();
$classRes = $classStmt->get_result();
$classRow = $classRes->fetch_assoc();
$class = $classRow['class'] ?? "";
foreach ($subjects as $subject => $marks) {



    // now insert marks
    $stmt = $conn->prepare("
INSERT INTO marks
(student_id,teacher_user_id,class,semester,subject,exam_type,total_marks,obtained_marks,status)
VALUES (?,?,?,? ,?,'FINAL',100,?,'published')
");

    $teacher = 0; // or valid teacher id

    $stmt->bind_param(
        "iisssi",
        $student_id,
        $teacher,
        $class,
        $semesterNumber,
        $subject,
        $marks
    );

    if (!$stmt->execute()) {
        file_put_contents("marks_error.txt", $stmt->error);
    }
}

/* ================= EXTRACT PERCENTAGE ================= */

/* ================= EXTRACT PERCENTAGE ================= */

/* ================= EXTRACT PERCENTAGE ================= */

$percentage = 0;

/* MSBTE style percentage detection */
if (preg_match('/PERCENTAGE\s*[:\-]?\s*([0-9]+\.[0-9]+)/i', $text, $m)) {
    $percentage = $m[1];
} elseif (preg_match('/([0-9]{2,3}\.[0-9]{2})/', $text, $m)) {
    $percentage = $m[1];
}

if ($percentage == 0) {
    echo json_encode([
        "status" => false,
        "message" => "Percentage not detected"
    ]);
    exit;

}
/* ================= SAVE RESULT ================= */

$stmt = $conn->prepare("
INSERT INTO semester_results
(student_id,semester,percentage,marksheet_pdf)
VALUES (?,?,?,?)
");

$stmt->bind_param(
    "iiss",
    $student_id,
    $semesterNumber,
    $percentage,
    $fileName
);

$stmt->execute();

/* ================= UPDATE STUDENT ================= */

$conn->query("
UPDATE students
SET marks_uploaded=1
WHERE user_id='$student_id'
");

/* ================= RESPONSE ================= */

echo json_encode([
    "status" => true,
    "message" => "Marksheet uploaded successfully",
    "semester" => $semesterNumber,
    "percentage" => $percentage
]);