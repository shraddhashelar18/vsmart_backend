<?php
//get_parent_dashboard.php
header("Content-Type: application/json");
require_once "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id'])) {
    echo json_encode(["status" => false, "message" => "user_id required"]);
    exit;
}

$user_id = $data['user_id'];

# Step 1: Get parent mobile
$parentQuery = $conn->prepare("SELECT mobile_no, full_name FROM parents WHERE user_id = ?");
$parentQuery->bind_param("i", $user_id);
$parentQuery->execute();
$parentResult = $parentQuery->get_result();

if ($parentResult->num_rows == 0) {
    echo json_encode(["status" => false, "message" => "Parent not found"]);
    exit;
}

$parent = $parentResult->fetch_assoc();
$parent_mobile = $parent['mobile_no'];

# Step 2: Get children
$studentsQuery = $conn->prepare("SELECT * FROM students WHERE parent_mobile_no = ?");
$studentsQuery->bind_param("s", $parent_mobile);
$studentsQuery->execute();
$studentsResult = $studentsQuery->get_result();

$children = [];

while ($student = $studentsResult->fetch_assoc()) {

    $student_id = $student['user_id'];
    $semester = $student['current_semester'];

    # -------- ATTENDANCE --------
    $attendanceQuery = $conn->prepare("
SELECT COUNT(*) as total,
SUM(CASE WHEN status='P' THEN 1 ELSE 0 END) as present_count
FROM attendance 
WHERE student_id=? AND semester=?
");
$attendanceQuery->bind_param("is", $student_id, $semester);
    
    $attendanceQuery->execute();
    $attendanceData = $attendanceQuery->get_result()->fetch_assoc();

    $attendance = 0;
    if ($attendanceData['total'] > 0) {
        $present = $attendanceData['present_count'] ?? 0;

$attendance = $present / $attendanceData['total'];
    }

    # -------- WEAK SUBJECTS --------
    $marksQuery = $conn->prepare("
        SELECT subject, exam_type, obtained_marks
        FROM marks
        WHERE student_id=? AND semester=?
    ");
   $marksQuery->bind_param("is", $student_id, $semester);
    $marksQuery->execute();
    $marksResult = $marksQuery->get_result();

    $ct1 = [];
    $ct2 = [];
    $weakSubjects = [];

    while ($m = $marksResult->fetch_assoc()) {

        $subject = $m['subject'];
        $marks = (int)($m['obtained_marks'] ?? 0);

        if ($m['exam_type'] == "CT1") {
            $ct1[$subject] = $marks;
        }

        if ($m['exam_type'] == "CT2") {
            $ct2[$subject] = $marks;
        }
    }

    $allSubjects = array_unique(array_merge(array_keys($ct1), array_keys($ct2)));

foreach ($allSubjects as $subject) {

    $marks = [];

    if (isset($ct1[$subject])) {
        $marks[] = $ct1[$subject];
    }

    if (isset($ct2[$subject])) {
        $marks[] = $ct2[$subject];
    }

    if (count($marks) > 0) {

        $avg = array_sum($marks) / count($marks);

        if ($avg < 15) {
            $weakSubjects[] = $subject;
        }
    }
}
$weakSubjects = array_unique($weakSubjects);

    $children[] = [
        "enrollment" => $student['enrollment_no'],
        "name" => $student['full_name'],
        "class" => $student['class'],
        "roll" => $student['roll_no'],
        "semester" => $student['current_semester'],
        "attendance" => $attendance,
        "weakSubjects" => $weakSubjects
    ];
}
echo json_encode([
    "status" => true,
    "parent_name" => $parent['full_name'],
    "children" => $children
]);
?>

