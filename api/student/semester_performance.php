<?php
include("../config.php");
header("Content-Type: application/json");

<<<<<<< HEAD
if(!isset($_POST['student_id'])){
=======
/* 🔹 ADD THIS PART HERE 🔹 */
$data = json_decode(file_get_contents("php://input"), true);

$student_id = intval($data['student_id'] ?? 0);

if($student_id <= 0){
>>>>>>> 3aba2f6ad2bf1196518bbd07f85dbfb78f698994
    echo json_encode([
        "status"=>false,
        "message"=>"student_id required"
    ]);
    exit;
}
<<<<<<< HEAD

$student_id = intval($_POST['student_id']);
=======
/* 🔹 END HERE 🔹 */
>>>>>>> 3aba2f6ad2bf1196518bbd07f85dbfb78f698994

$sql = "SELECT semester, percentage 
        FROM semester_results 
        WHERE student_id=? 
        ORDER BY semester ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$student_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];

while($row = $result->fetch_assoc()){
    $data[] = $row;
}

echo json_encode([
    "status"=>true,
    "student_id"=>$student_id,
    "semesters"=>$data
]);
<<<<<<< HEAD
?>
=======
?>
>>>>>>> 3aba2f6ad2bf1196518bbd07f85dbfb78f698994
