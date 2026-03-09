<?php
require_once("../config.php");
require_once("../promotion_helper.php");
require_once("../api_guard.php");
require_once("../cors.php");
header("Content-Type: application/json");

/* ================= ROLE CHECK ================= */

if($currentRole!="admin"){
echo json_encode(["status"=>false,"message"=>"Access denied"]);
exit;
}

/* ================= SETTINGS ================= */

$setting=$conn->query("
SELECT final_published,promotion_done,atkt_limit
FROM settings LIMIT 1
");

$row=$setting->fetch_assoc();

if($row['promotion_done']==1){
echo json_encode([
"status"=>false,
"message"=>"Promotion already executed"
]);
exit;
}

/* ================= CHECK MARKS UPLOAD ================= */

$missing=$conn->query("
SELECT user_id FROM students
WHERE marks_uploaded=0
AND status!='detained'
");

if($missing->num_rows>0){
echo json_encode([
"status"=>false,
"message"=>"Some students have not uploaded marksheets"
]);
exit;
}

/* ================= START PROMOTION ================= */

$conn->query("UPDATE settings SET final_published=1");

$atktLimit=$row['atkt_limit'];

$students=$conn->query("
SELECT user_id,class,current_semester,status
FROM students
");

/* ================= LOOP STUDENTS ================= */

while($student=$students->fetch_assoc()){

/* Skip already detained students */

if($student['status']=="detained"){
continue;
}

$id=$student['user_id'];

$promotion=calculatePromotion($conn,$id,$atktLimit);

/* Skip students with no marks */

if($promotion['percentage']==null && $promotion['backlogCount']==0){
continue;
}

$class=$student['class'];
$sem=(int)preg_replace('/[^0-9]/','',$student['current_semester']);

$newClass=$class;
$newSem=$sem;

/* ================= SEM 1–5 ================= */

if($sem < 6){

if(
$promotion['status']=="PROMOTED" ||
$promotion['status']=="PROMOTED_WITH_ATKT"
){

$newSem=$sem+1;

$dept=substr($class,0,2);
$div=substr($class,-2);

$newClass=$dept.$newSem.$div;

}

}

/* ================= SEM 6 ================= */

else{

if($promotion['status']=="PROMOTED"){
$promotion['status']="PASSED_OUT";
}

}

/* ================= UPDATE STUDENT ================= */

$stmt=$conn->prepare("
UPDATE students
SET status=?,current_semester=?,class=?
WHERE user_id=?
");

$stmt->bind_param(
"sssi",
$promotion['status'],
$newSem,
$newClass,
$id
);

$stmt->execute();

}

/* ================= COMPLETE PROMOTION ================= */

$conn->query("
UPDATE settings
SET promotion_done=1
");

echo json_encode([
"status"=>true,
"message"=>"Promotion completed"
]);

?>