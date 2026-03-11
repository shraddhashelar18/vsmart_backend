<?php
require_once("../config.php");
require_once("../promotion_helper.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

if($currentRole!="admin"){
echo json_encode(["status"=>false,"message"=>"Access denied"]);
exit;
}

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

/* check missing uploads */

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

$conn->query("UPDATE settings SET final_published=1");

$atktLimit=$row['atkt_limit'];

$students=$conn->query("
SELECT user_id,class,current_semester
FROM students
");

while($student=$students->fetch_assoc()){

$id=$student['user_id'];

$promotion=calculatePromotion($conn,$id,$atktLimit);

$class=$student['class'];
$sem=(int)preg_replace('/[^0-9]/','',$student['current_semester']);

$newClass=$class;
$newSem=$sem;

/* ===== PROMOTION LOGIC ===== */

if($promotion['status']=="PROMOTED" || 
   $promotion['status']=="PROMOTED_WITH_ATKT"){

    /* SEMESTER 1–5 */

    if($sem < 6){

        $newSem = $sem + 1;

<<<<<<< HEAD
=======
        $dept = substr($class,0,2);
        $div = substr($class,-2);

        $newClass = $dept.$newSem.$div;

    }

    /* SEMESTER 6 (FINAL) */

    else{

        /* Only fully passed students graduate */

        if($promotion['status']=="PROMOTED"){
            $promotion['status'] = "PASSED_OUT";
        }

        /* ATKT students remain in SEM6 */
        else if($promotion['status']=="PROMOTED_WITH_ATKT"){
            $promotion['status'] = "PROMOTED_WITH_ATKT";
            $newSem = 6;
            $newClass = $class;
        }

    }

>>>>>>> e6a5f178130228e6f5e713abe60623585768e6a2
}

/* ===== UPDATE STUDENT ===== */

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

/* mark promotion completed */

$conn->query("
UPDATE settings
SET promotion_done=1
");

echo json_encode([
"status"=>true,
"message"=>"Promotion completed"
]);