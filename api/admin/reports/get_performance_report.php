<?php
require_once("../../config.php");
require_once("../../api_guard.php");
require_once("../../cors.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$class = $data['class'];
$exam = $data['exam'];
$action = $data['action'] ?? '';
/* =========================
   GET SUBJECT CLASS (IF6K)
========================= */

$subjectClass = substr($class, 0, 4);   // IF6KA -> IF6K


/* =========================
   TOTAL SUBJECTS
========================= */

$subjectQuery = $conn->prepare("
SELECT COUNT(*) as total_subjects
FROM semester_subjects
WHERE class = ?
");

$subjectQuery->bind_param("s", $subjectClass);
$subjectQuery->execute();

$subjectResult = $subjectQuery->get_result()->fetch_assoc();
$totalSubjects = $subjectResult['total_subjects'] ?? 0;


/* =========================
   PUBLISHED SUBJECTS
========================= */

/* =========================
   COUNT PUBLISHED THEORY SUBJECTS
========================= */

$publishedQuery = $conn->prepare("
SELECT COUNT(DISTINCT m.subject) as published_count
FROM marks m
INNER JOIN semester_subjects ss 
ON m.subject = ss.subject_name
WHERE m.class = ?
AND m.exam_type = ?
AND m.status = 'published'
AND ss.class = ?
");

$publishedQuery->bind_param("sss", $class, $exam, $subjectClass);
$publishedQuery->execute();

$publishedResult = $publishedQuery->get_result()->fetch_assoc();
$publishedSubjects = $publishedResult['published_count'] ?? 0;


/* =========================
   CHECK IF ALL PUBLISHED
========================= */

if ($publishedSubjects != $totalSubjects) {

    echo json_encode([
        "status" => false,
        "message" => "Marks will appear after all teachers publish marks ($publishedSubjects/$totalSubjects)"
    ]);

    exit;
}

if ($action == "check_exam") {

    $class = $data['class'];
    $exam = $data['exam'];

    $subjectClass = substr($class, 0, 4);

    // TOTAL SUBJECTS
    $totalQuery = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM semester_subjects 
        WHERE class = ?
    ");
    $totalQuery->bind_param("s", $subjectClass);
    $totalQuery->execute();
    $totalSubjects = $totalQuery->get_result()->fetch_assoc()['total'];

    // PUBLISHED SUBJECTS (FOR SAME EXAM)
    $pubQuery = $conn->prepare("
        SELECT COUNT(DISTINCT m.subject) as published
        FROM marks m
        INNER JOIN semester_subjects ss 
        ON m.subject = ss.subject_name
        WHERE m.class = ?
        AND m.exam_type = ?
        AND m.status = 'published'
        AND ss.class = ?
    ");

    $pubQuery->bind_param("sss", $class, $exam, $subjectClass);
    $pubQuery->execute();
    $published = $pubQuery->get_result()->fetch_assoc()['published'];

    $enabled = ($published == $totalSubjects);

    echo json_encode([
        "status" => true,
        "enabled" => $enabled,
        "published" => $published,
        "total" => $totalSubjects
    ]);
    exit;
}
/* =========================
   FETCH STUDENT MARKS
========================= */

$stmt = $conn->prepare("
SELECT 
    s.user_id,
    s.full_name,
    SUM(m.obtained_marks) AS obtained
FROM students s

LEFT JOIN marks m 
ON s.user_id = m.student_id
AND m.class = ?
AND m.exam_type = ?
AND m.status = 'published'

INNER JOIN semester_subjects ss
ON ss.subject_name = m.subject
AND ss.class = ?

WHERE s.class = ?

GROUP BY s.user_id
");

$stmt->bind_param("ssss", $class, $exam, $subjectClass, $class);
$stmt->execute();

$result = $stmt->get_result();

$students = [];

while ($row = $result->fetch_assoc()) {

    $student = [];
    $student['name'] = $row['full_name'];

    $maxPerSubject = ($exam == "FINAL") ? 100 : 30;
    $student['max'] = $totalSubjects * $maxPerSubject;

    $obtained = $row['obtained'];

    if ($obtained === null) {
        $obtainedValue = "Absent";
    } else {
        $obtainedValue = (int) $obtained;
    }

    if ($exam == "CT1") {
        $student['ct1_total'] = $obtainedValue;
        $student['ct2_total'] = null;
    } else {
        $student['ct1_total'] = null;
        $student['ct2_total'] = $obtainedValue;
    }

    if (is_numeric($obtainedValue)) {
        $student['percentage'] = round(
            ($obtainedValue / $student['max']) * 100,
            2
        );
    } else {
        $student['percentage'] = 0;
    }

    $students[] = $student;
}

echo json_encode([
    "status" => true,
    "students" => $students
]);