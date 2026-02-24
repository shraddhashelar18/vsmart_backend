<?php
require_once("../../config.php");
require_once("../../api_guard.php");

header("Content-Type: application/json");

$data=json_decode(file_get_contents("php://input"),true);

$department=$data['department'];

$semRow=$conn->query("SELECT active_semester FROM settings LIMIT 1")->fetch_assoc();
$active=$semRow['active_semester'];

if($active=="EVEN"){
    $stmt=$conn->prepare("SELECT class_name FROM classes WHERE department_code=? AND semester IN (2,4,6)");
}else{
    $stmt=$conn->prepare("SELECT class_name FROM classes WHERE department_code=? AND semester IN (1,3,5)");
}

$stmt->bind_param("s",$department);
$stmt->execute();
$res=$stmt->get_result();

$classes=[];
while($r=$res->fetch_assoc()){
    $classes[]=$r['class_name'];
}

echo json_encode(["status"=>true,"classes"=>$classes]);
