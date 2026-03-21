<?php
//get_detained_classes.php
require_once("../config.php");
require_once("../api_guard.php");
require_once("../cors.php");

header("Content-Type: application/json");

if ($currentRole != "hod" && $currentRole != "principal") {
    echo json_encode([
        "status"=>false,
        "message"=>"Access Denied"
    ]);
    exit;
}

$data=json_decode(file_get_contents("php://input"),true);

$department=$data['department'];

$setting=$conn->query("
SELECT active_semester
FROM settings
LIMIT 1
");

$active=$setting->fetch_assoc()['active_semester'];

if($active=="EVEN"){

    $stmt=$conn->prepare("
    SELECT class_name
    FROM classes
    WHERE department=?
    AND semester IN (1,3,5)
    ORDER BY semester,class_name
    ");

}else{

    $stmt=$conn->prepare("
    SELECT class_name
    FROM classes
    WHERE department=?
    AND semester IN (2,4,6)
    ORDER BY semester,class_name
    ");

}

$stmt->bind_param("s",$department);
$stmt->execute();

$result=$stmt->get_result();

$classes=[];

while($row=$result->fetch_assoc()){
    $classes[]=$row['class_name'];
}

echo json_encode([
"status"=>true,
"classes"=>$classes
]);