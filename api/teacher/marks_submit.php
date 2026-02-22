<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

/* ========= INPUTS ========= */

$class           = $data['class'] ?? '';
$subject         = $data['subject'] ?? '';
$exam_type       = strtoupper($data['exam_type'] ?? '');
$semester        = $data['semester'] ?? '';
$teacher_user_id = $data['teacher_user_id'] ?? '';
$marks_list      = $data['marks'] ?? [];

if (
    $class === '' || 
    $subject === '' || 
    $exam_type === '' || 
    $semester === '' ||
    $teacher_user_id === ''
) {
    echo json_encode([
        "status" => false,
        "message" => "Missing required fields"
    ]);
    exit;
}

/* ========= EXAM TYPE VALIDATION ========= */

$allowed_exams = ['CT1', 'CT2', 'FINAL'];

if (!in_array($exam_type, $allowed_exams)) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid exam type"
    ]);
    exit;
}

/* ========= DEFAULT TOTAL MARKS ========= */

if ($exam_type === 'CT1' || $exam_type === 'CT2') {
    $total_marks = 30;
} else {
    $total_marks = 100;
}

/* ========= PREPARE INSERT ========= */

$stmt = $conn->prepare(
    "INSERT INTO marks
     (student_id, teacher_user_id, class, subject, exam_type, semester, total_marks, obtained_marks)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);

/* ========= LOOP STUDENTS ========= */

foreach ($marks_list as $row) {

    $student_id = $row['student_id'];
    $obtained_marks  = $row['obtained_marks'];

    if ($obtained_marks > $total_marks) {
        echo json_encode([
            "status" => false,
            "message" => "Obtained marks cannot exceed total marks"
        ]);
        exit;
    }

    $stmt->bind_param(
        "iissssii",
        $student_id,
        $teacher_user_id,
        $class,
        $subject,
        $exam_type,
        $semester,
        $total_marks,
        $obtained_marks
    );

    $stmt->execute();

    /* ===== CALCULATE SEMESTER RESULT FOR THIS STUDENT ===== */

    $stmtCalc = $conn->prepare("
        SELECT 
            SUM(obtained_marks) as total_obtained,
            SUM(total_marks) as total_all_marks
        FROM marks
        WHERE student_id = ?
        AND semester = ?
    ");

    $stmtCalc->bind_param("is", $student_id, $semester);
    $stmtCalc->execute();
    $result = $stmtCalc->get_result()->fetch_assoc();

    $total_obtained = $result['total_obtained'] ?? 0;
    $total_all_marks = $result['total_all_marks'] ?? 0;

    if ($total_all_marks > 0) {

        $percentage = ($total_obtained / $total_all_marks) * 100;

        $stmt2 = $conn->prepare("
            INSERT INTO semester_results (student_id, semester, percentage)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE percentage = ?
        ");

        $stmt2->bind_param(
            "isdd",
            $student_id,
            $semester,
            $percentage,
            $percentage
        );

        $stmt2->execute();
    }
}

echo json_encode([
    "status" => true,
    "message" => "Marks submitted & semester result updated"
]);