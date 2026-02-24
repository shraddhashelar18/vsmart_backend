<?php
require_once("../../config.php");
require_once("../../api_guard.php");
require_once("../../helpers/promotion_helper.php");

header("Content-Type: application/json");

/* Department comes from secure token */
$department = $currentDepartment;

/* Get active semester + ATKT limit */
$setting = $conn->query("SELECT active_semester, atkt_limit FROM settings LIMIT 1");
$settingRow = $setting->fetch_assoc();

$activeSemester = $settingRow['active_semester'];
$atktLimit = $settingRow['atkt_limit'];

$semesterList = ($activeSemester == "EVEN") ? "(2,4,6)" : "(1,3,5)";

/* Get active classes */
$classQuery = $conn->query("
SELECT class_name FROM classes
WHERE department = '$department'
AND semester IN $semesterList
");

$classNames = [];
while($c = $classQuery->fetch_assoc()){
    $classNames[] = "'".$c['class_name']."'";
}

if(empty($classNames)){
    echo json_encode([
        "status"=>true,
        "totalStudents"=>0,
        "totalTeachers"=>0,
        "promoted"=>0,
        "promotedWithBacklog"=>0,
        "detained"=>0
    ]);
    exit;
}

$classString = implode(",", $classNames);

/* Get students */
$studentsQuery = $conn->query("
SELECT enrollment_no FROM students
WHERE class_name IN ($classString)
");

$totalStudents = $studentsQuery->num_rows;

/* Count teachers */
$stmtTeacher = $conn->prepare("
SELECT COUNT(*) as total FROM teachers
WHERE department = ?
");
$stmtTeacher->bind_param("s",$department);
$stmtTeacher->execute();
$totalTeachers = $stmtTeacher->get_result()->fetch_assoc()['total'];

$promoted = 0;
$atkt = 0;
$detained = 0;

while($student = $studentsQuery->fetch_assoc()){

    $result = calculatePromotion($conn, $student['enrollment_no'], $atktLimit);

    if($result['status'] == "PROMOTED") $promoted++;
    elseif($result['status'] == "PROMOTED_WITH_ATKT") $atkt++;
    else $detained++;
}

echo json_encode([
    "status"=>true,
    "totalStudents"=>$totalStudents,
    "totalTeachers"=>$totalTeachers,
    "promoted"=>$promoted,
    "promotedWithBacklog"=>$atkt,
    "detained"=>$detained
]);

