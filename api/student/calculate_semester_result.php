<?php
include("../config.php");
header("Content-Type: application/json");

/* =========================
   CHECK INPUT
========================= */

if(!isset($_POST['student_id']) || !isset($_POST['semester'])){
    echo json_encode([
        "status"=>false,
        "message"=>"student_id and semester required"
    ]);
    exit;T
}

$student_id = intval($_POST['student_id']);
$semester   = $_POST['semester'];

/* =========================
   CALCULATE TOTAL
========================= */

$sql = "SELECT obtained_marks, total_marks 
        FROM marks 
        WHERE student_id=? AND semester=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is",$student_id,$semester);
$stmt->execute();
$result = $stmt->get_result();

$total_obtained = 0;
$total_max = 0;

while($row = $result->fetch_assoc()){
    $total_obtained += $row['obtained_marks'];
    $total_max += $row['total_marks'];
}

if($total_max == 0){
    echo json_encode([
        "status"=>false,
        "message"=>"No marks found"
    ]);
    exit;
}

$percentage = round(($total_obtained/$total_max)*100,2);

/* =========================
   INSERT OR UPDATE
========================= */

$check = $conn->prepare("SELECT id FROM semester_results WHERE student_id=? AND semester=?");
$check->bind_param("is",$student_id,$semester);
$check->execute();
$check_result = $check->get_result();

if($check_result->num_rows > 0){

    $update = $conn->prepare("UPDATE semester_results 
                              SET percentage=? 
                              WHERE student_id=? AND semester=?");
    $update->bind_param("dis",$percentage,$student_id,$semester);
    $update->execute();

}else{

    $insert = $conn->prepare("INSERT INTO semester_results 
                              (student_id, semester, percentage) 
                              VALUES (?,?,?)");
    $insert->bind_param("isd",$student_id,$semester,$percentage);
    $insert->execute();
}

echo json_encode([
    "status"=>true,
    "student_id"=>$student_id,
    "semester"=>$semester,
    "percentage"=>$percentage
]);
?>
