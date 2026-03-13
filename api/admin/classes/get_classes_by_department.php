<?php
require_once("../../config.php");
require_once("../../api_guard.php");
require_once("../../cors.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$dept = $data['department'];

$setting = $conn->query("SELECT active_semester FROM settings LIMIT 1");
$row = $setting->fetch_assoc();
$active = $row['active_semester'];

if ($active == "EVEN") {
    $stmt = $conn->prepare("
        SELECT 
            c.class_name,
            c.class_teacher AS teacher_id,
            t.full_name AS teacher_name
        FROM classes c
        LEFT JOIN teachers t ON t.user_id = c.class_teacher
        WHERE c.department=? AND c.semester IN (2,4,6)
        ORDER BY c.semester ASC, c.class_name ASC
    ");
} else {
    $stmt = $conn->prepare("
        SELECT 
            c.class_name,
            c.class_teacher AS teacher_id,
            t.full_name AS teacher_name
        FROM classes c
        LEFT JOIN teachers t ON t.user_id = c.class_teacher
        WHERE c.department=? AND c.semester IN (1,3,5)
        ORDER BY c.semester ASC, c.class_name ASC
    ");
}

$stmt->bind_param("s",$dept);
$stmt->execute();
$res = $stmt->get_result();

$classes=[];

while($r=$res->fetch_assoc()){
    $classes[] = [
        "class_name" => $r["class_name"],
        "teacher_id" => intval($r["teacher_id"]),
        "teacher_name" => $r["teacher_name"] ?? ""
    ];
}

echo json_encode([
    "status"=>true,
    "classes"=>$classes
]);