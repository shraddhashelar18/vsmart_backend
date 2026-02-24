<?php
require_once("../../config.php");
require_once("../../api_guard.php");

header("Content-Type: application/json");

$data=json_decode(file_get_contents("php://input"),true);
$className=$data['className'];

$stmt=$conn->prepare("
SELECT full_name,roll_no,enrollment_no,mobile_no,parent_mobile_no,email
FROM students
WHERE class_name=?
ORDER BY roll_no ASC
");

$stmt->bind_param("s",$className);
$stmt->execute();
$res=$stmt->get_result();

$students=[];

while($r=$res->fetch_assoc()){
    $students[]=[
        "name"=>$r['full_name'],
        "rollNo"=>$r['roll_no'],
        "enrollmentNo"=>$r['enrollment_no'],
        "phone"=>$r['mobile_no'],
        "parentMobile"=>$r['parent_mobile_no'],
        "email"=>$r['email']
    ];
}

echo json_encode(["status"=>true,"students"=>$students]);
