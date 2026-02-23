<?php
include("../../config.php");
header("Content-Type: application/json");

if(!isset($_POST['user_id']) || !isset($_FILES['file'])){
    echo json_encode([
        "status"=>false,
        "message"=>"student_id and file required"
    ]);
    exit;
}

$student_id = intval($_POST['user_id']);

$target_dir = __DIR__ . "/uploads/";

if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$filename = time() . "_" . basename($_FILES["file"]["name"]);
$target_file = $target_dir . $filename;

if(move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)){

    $stmt = $conn->prepare("UPDATE students SET final_marksheet=? WHERE user_id=?");
    $stmt->bind_param("si", $filename, $user_id);
    $stmt->execute();

    echo json_encode([
        "status"=>true,
        "message"=>"Uploaded successfully",
        "file_name"=>$filename
    ]);

}else{
    echo json_encode([
        "status"=>false,
        "message"=>"Upload Failed"
    ]);
}
?>
