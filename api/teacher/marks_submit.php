<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

<<<<<<< HEAD
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "status" => false,
        "message" => "Method not allowed"
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

/* -------- GET DATA -------- */

$class           = trim($data['class'] ?? '');
$semester        = trim($data['semester'] ?? '');
$subject_name    = trim($data['subject_name'] ?? '');
$exam_type       = trim($data['exam_type'] ?? '');
$teacher_user_id = intval($data['teacher_user_id'] ?? 0);
$marks_list      = $data['marks'] ?? [];

/* -------- VALIDATION -------- */

if (
    $class === '' ||
    $semester === '' ||
    $subject_name === '' ||
    $exam_type === '' ||
    $teacher_user_id <= 0 ||
    empty($marks_list)
=======
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
>>>>>>> 3aba2f6ad2bf1196518bbd07f85dbfb78f698994
) {
    echo json_encode([
        "status" => false,
        "message" => "Missing required fields"
    ]);
    exit;
}

<<<<<<< HEAD
/* -------- VALIDATE EXAM TYPE -------- */

$allowed_exam_types = ['CT1','CT2','FINAL'];

if (!in_array($exam_type, $allowed_exam_types)) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid exam type"
    ]);
    exit;
}

/* -------- AUTO SET TOTAL MARKS -------- */

$total_marks = ($exam_type === "FINAL") ? 100 : 30;

/* -------- START TRANSACTION -------- */

$conn->begin_transaction();

try {

    $insert = $conn->prepare(
        "INSERT INTO marks
        (student_id, teacher_user_id, class, semester, subject_name, exam_type, total_marks, obtained_marks)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );

    foreach ($marks_list as $row) {

        $student_id     = intval($row['student_id']);
        $obtained_marks = intval($row['obtained_marks']);

        if ($obtained_marks > $total_marks) {
            throw new Exception("Marks cannot be greater than $total_marks");
        }

        /* ----- DUPLICATE CHECK WITH exam_type ----- */

        $check = $conn->prepare(
            "SELECT id FROM marks
             WHERE student_id = ?
             AND class = ?
             AND semester = ?
             AND subject_name = ?
             AND exam_type = ?"
        );

        $check->bind_param(
            "issss",
            $student_id,
            $class,
            $semester,
            $subject_name,
            $exam_type
        );

        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            throw new Exception("Marks already submitted for this exam");
        }

        /* ----- INSERT ----- */

        $insert->bind_param(
            "iisssiii",
            $student_id,
            $teacher_user_id,
            $class,
            $semester,
            $subject_name,
            $exam_type,
            $total_marks,
            $obtained_marks
        );

        $insert->execute();
    }

    $conn->commit();

    echo json_encode([
        "status" => true,
        "message" => "$exam_type marks submitted successfully"
    ]);

} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);
}
=======
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
>>>>>>> 3aba2f6ad2bf1196518bbd07f85dbfb78f698994
