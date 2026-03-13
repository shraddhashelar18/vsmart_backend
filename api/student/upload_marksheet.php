<?php
require_once("../config.php");
require_once("../api_guard.php");
require_once("../cors.php");
header("Content-Type: application/json");

if($currentRole!="student"){
    echo json_encode(["status"=>false,"message"=>"Access denied"]);
    exit;
}

$userId=$currentUserId;

/* check upload allowed */

$setting=$conn->query("SELECT allow_marksheet_upload FROM settings LIMIT 1");
$row=$setting->fetch_assoc();

if($row['allow_marksheet_upload']==0){
    echo json_encode(["status"=>false,"message"=>"Upload not allowed"]);
    exit;
}

/* prevent reupload */

$check=$conn->query("
SELECT marks_uploaded,current_semester
FROM students
WHERE user_id='$userId'
")->fetch_assoc();

if($check['marks_uploaded']==1){
    echo json_encode(["status"=>false,"message"=>"Already uploaded"]);
    exit;
}

$semester="SEM".$check['current_semester'];

/* assume PDF parsed already */

$marks=[
 ["subject"=>"Machine Learning","marks"=>78],
 ["subject"=>"Computer Networks","marks"=>65],
 ["subject"=>"Operating System","marks"=>55]
];

foreach($marks as $m){

    $stmt=$conn->prepare("
        INSERT INTO marks
        (student_id,semester,subject,exam_type,total_marks,obtained_marks)
        VALUES (?,?,?,?,?,?)
    ");

    $exam="FINAL";
    $total=100;

    $stmt->bind_param(
        "isssii",
        $userId,
        $semester,
        $m['subject'],
        $exam,
        $total,
        $m['marks']
    );

    $stmt->execute();
}

/* mark upload complete */

$conn->query("
UPDATE students
SET marks_uploaded=1
WHERE user_id='$userId'
");

echo json_encode([
    "status"=>true,
    "message"=>"Marksheet uploaded"
]);