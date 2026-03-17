<?php

require_once("../config.php");
require_once("../api_guard.php");
require_once("../cors.php");
require_once("../../vendor/autoload.php");

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

$uploadDir = "../uploads/marksheets/";

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$fileName = $student_id . "_" . time() . ".pdf";
$target = $uploadDir . $fileName;

if (!move_uploaded_file($file['tmp_name'], $target)) {
    echo json_encode([
        "status" => false,
        "message" => "Failed to save file"
    ]);
    exit;
}

/* ================= READ PDF ================= */

try {

    $parser = new Parser();
    $pdf = $parser->parseFile($target);
    $text = $pdf->getText();
    file_put_contents("debug.txt", $text);

} catch (Exception $e) {

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

/* ================= EXTRACT MARKS ================= */

$subjectList = [];

while ($row = $res->fetch_assoc()) {
    $subjectList[] = $row['subject_name'];
}

$lines = preg_split("/\r\n|\n|\r/", $text);

foreach ($lines as $line) {

    foreach ($subjectList as $subject) {

        if (stripos($line, $subject) !== false) {

            preg_match_all('/\d+/', $line, $nums);

            if (!empty($nums[0])) {

                $numbers = $nums[0];

                for ($i = 0; $i < count($numbers); $i++) {

                    if ($numbers[$i] == 100 && isset($numbers[$i + 1])) {

                        $marks = intval($numbers[$i + 1]);

                        if ($marks <= 100) {
                            $subjects[$subject] = $marks;
                        }

                        break;
                    }

                }
            }
        }
    }
}

/* ================= SAVE MARKS ================= */

foreach ($subjects as $subject => $marks) {

    $stmt = $conn->prepare("
    INSERT INTO marks
    (student_id,teacher_user_id,class,semester,subject,exam_type,total_marks,obtained_marks,status)
    VALUES (?,?,?,? ,?,'FINAL',100,?,'published')
    ");

    $teacher = 0;
    $class = "";

    $stmt->bind_param(
        "iisssi",
        $student_id,
        $teacher,
        $class,
        $semesterNumber,
        $subject,
        $marks
    );

    $stmt->execute();
}

/* ================= EXTRACT PERCENTAGE ================= */

/* ================= EXTRACT PERCENTAGE ================= */

/* ================= EXTRACT PERCENTAGE ================= */

$percentage = 0;

/* MSBTE style percentage detection */
if (preg_match('/PERCENTAGE\s*[:\-]?\s*([0-9]+\.[0-9]+)/i', $text, $m)) {
    $percentage = $m[1];
}
elseif (preg_match('/([0-9]{2,3}\.[0-9]{2})/', $text, $m)) {
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

?>