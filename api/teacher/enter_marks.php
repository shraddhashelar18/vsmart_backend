<?php
//enter_marks.php
require_once("../config.php");
require_once("../api_guard.php");
require_once("../cors.php");
header("Content-Type: application/json");
if($currentRole != "teacher"){
    echo json_encode([
        "status" => false,
        "message" => "Access denied"
    ]);
    exit;
}
$data = json_decode(file_get_contents("php://input"), true);

$class = $data['class'] ?? '';
$subject = trim($data['subject'] ?? '');
$examType = $data['exam_type'] ?? '';
$totalMarks = $data['total_marks'] ?? 30;
$isDraft = $data['is_draft'] ?? true;
$marksList = $data['marks'] ?? [];

if (!$class || !$subject || !$examType) {
    echo json_encode([
        "status" => false,
        "message" => "Class, Subject and Exam Type required"
    ]);
    exit;
}

/* =========================
   CHECK SUBJECT EXISTS
========================= */

$prefix = substr($class, 0, 4); // IF6KA -> IF6K

$subjectCheck = $conn->prepare("
SELECT subject_name
FROM semester_subjects
WHERE class = ?
AND subject_name = ?
");

$subjectCheck->bind_param("ss", $prefix, $subject);
$subjectCheck->execute();
$subjectRes = $subjectCheck->get_result();

if ($subjectRes->num_rows == 0) {
    echo json_encode([
        "status" => false,
        "message" => "Subject not found for this class"
    ]);
    exit;
}

/* =========================
   CHECK MARKS LIST
========================= */

if (empty($marksList)) {
    echo json_encode([
        "status" => false,
        "message" => "No marks received"
    ]);
    exit;
}

foreach ($marksList as $item) {

    $studentUserId = $item['user_id'] ?? '';
    $obtainedMarks = $item['obtained_marks'] ?? '';
    $semester = $item['semester'] ?? '';

    if (!$studentUserId || !$semester) continue;

    /* =========================
       HANDLE BLANK MARKS
    ========================= */

   if ($obtainedMarks === "" || $obtainedMarks === null) {

    $obtainedMarks = null;   // 🔥 keep it NULL instead of 0

    if ($isDraft) {
        $status = "draft";
    } else {
        $status = "published";
    }

} else {

        if ($obtainedMarks > $totalMarks) {
            echo json_encode([
                "status" => false,
                "message" => "Marks cannot exceed total marks"
            ]);
            exit;
        }

        $status = $isDraft ? "draft" : "published";
    }

    /* =========================
       CHECK EXISTING MARK
    ========================= */

    $check = $conn->prepare("
    SELECT id FROM marks
    WHERE student_id=? AND subject=? AND exam_type=? AND class=?
    ");

    $check->bind_param("isss", $studentUserId, $subject, $examType, $class);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {

        /* UPDATE */

        $update = $conn->prepare("
        UPDATE marks
        SET obtained_marks=?, total_marks=?, semester=?, status=?
        WHERE student_id=? AND subject=? AND exam_type=? AND class=?
        ");

$update->bind_param(
"iiisisss",
$obtainedMarks,
$totalMarks,
$semester,
$status,
$studentUserId,
$subject,
$examType,
$class
);

        $update->execute();

    } else {

        /* INSERT */

        $insert = $conn->prepare("
INSERT INTO marks
(student_id, teacher_user_id, class, semester, subject, exam_type, total_marks, obtained_marks, status)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$insert->bind_param(
"iissssiis",
$studentUserId,
$currentUserId,
$class,
$semester,
$subject,
$examType,
$totalMarks,
$obtainedMarks,
$status
);
        $insert->execute();
    }
}

echo json_encode([
    "status" => true,
    "message" => $isDraft ? "Draft saved successfully" : "Marks published successfully"
]);
?>