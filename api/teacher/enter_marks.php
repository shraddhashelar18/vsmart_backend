<?php
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

/* ================= INPUT ================= */

$data = json_decode(file_get_contents("php://input"), true);

$class = $data['class'] ?? '';
$subject = $data['subject'] ?? '';
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

/* ================= CHECK SUBJECT EXISTS ================= */

$prefix = substr($class, 0, 4);

$subjectCheck = $conn->prepare("
SELECT subject_name
FROM semester_subjects
WHERE class LIKE CONCAT(?, '%')
AND subject_name=?
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

/* ================= CHECK MARKS LIST ================= */

if (empty($marksList)) {
    echo json_encode([
        "status" => false,
        "message" => "No marks received"
    ]);
    exit;
}

/* ================= SAVE MARKS ================= */

foreach ($marksList as $item) {

    $studentUserId = $item['user_id'] ?? '';
    $obtainedMarks = $item['obtained_marks'] ?? '';
    $semester = $item['semester'] ?? '';

    if (!$studentUserId || !$semester) continue;

    /* ================= HANDLE BLANK MARKS ================= */

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

    /* ================= CHECK EXISTING MARK ================= */

    $check = $conn->prepare("
    SELECT id, status
    FROM marks
    WHERE student_id=? AND subject=? AND exam_type=? AND class=?
    ");

    $check->bind_param("isss", $studentUserId, $subject, $examType, $class);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {

        $existing = $result->fetch_assoc();

        /* ================= BLOCK EDIT IF PUBLISHED ================= */

        if ($existing['status'] == "published") {
            echo json_encode([
                "status" => false,
                "message" => "Marks already published and cannot be modified"
            ]);
            exit;
        }

        /* ================= UPDATE MARK ================= */

        $update = $conn->prepare("
        UPDATE marks
        SET obtained_marks=?, total_marks=?, semester=?, status=?
        WHERE student_id=? AND subject=? AND exam_type=? AND class=?
        ");

        $update->bind_param(
          "sississs",
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

        /* ================= INSERT MARK ================= */

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

<<<<<<< HEAD
=======
$insert->bind_param(
"iisssssis",
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
>>>>>>> 0bd6972add62d72da43eee285cf165c873b210c1
        $insert->execute();
    }
}

/* ================= CHECK CLASS RESULT STATUS ================= */

if (!$isDraft) {

    $prefix = substr($class, 0, 4);

    $subjectCountQuery = $conn->prepare("
    SELECT COUNT(*) as total_subjects
    FROM semester_subjects
    WHERE class LIKE CONCAT(?, '%')
    ");

    $subjectCountQuery->bind_param("s", $prefix);
    $subjectCountQuery->execute();
    $subjectCountResult = $subjectCountQuery->get_result()->fetch_assoc();

    $totalSubjects = $subjectCountResult['total_subjects'];

    $publishedQuery = $conn->prepare("
    SELECT COUNT(DISTINCT subject) as published_subjects
    FROM marks
    WHERE class=? AND exam_type=? AND status='published'
    ");

    $publishedQuery->bind_param("ss", $class, $examType);
    $publishedQuery->execute();
    $publishedResult = $publishedQuery->get_result()->fetch_assoc();

    $publishedSubjects = $publishedResult['published_subjects'];

    if ($publishedSubjects == $totalSubjects) {

        if ($examType == "CT1") {

            $updateClass = $conn->prepare("
            UPDATE classes
            SET ct1_published = 1
            WHERE class_name = ?
            ");

            $updateClass->bind_param("s", $class);
            $updateClass->execute();

        }

        if ($examType == "CT2") {

            $updateClass = $conn->prepare("
            UPDATE classes
            SET ct2_published = 1
            WHERE class_name = ?
            ");

            $updateClass->bind_param("s", $class);
            $updateClass->execute();
        }
    }
}

/* ================= FINAL RESPONSE ================= */

echo json_encode([
    "status" => true,
    "message" => $isDraft ? "Draft saved successfully" : "Marks published successfully"
]);

?>