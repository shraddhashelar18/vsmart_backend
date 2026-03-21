<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../../config.php");

$user_id = $_POST['user_id'];
$department = $_POST['department'];
$class = $_POST['class'];
$subjects = $_POST['subjects'] ?? [];

foreach($subjects as $sub){

    // ✅ CHECK IF ALREADY ASSIGNED (ANY TEACHER)
    $check = $conn->prepare("
        SELECT id FROM teacher_assignments 
        WHERE class=? AND subject=? AND status='active'
    ");

    if(!$check){
        die("Check Error: " . $conn->error);
    }

    $check->bind_param("ss", $class, $sub);
    $check->execute();

    if($check->get_result()->num_rows > 0){
        continue; // skip duplicate
    }

    // ✅ INSERT
    $stmt = $conn->prepare("
        INSERT INTO teacher_assignments 
        (user_id, department, class, subject, status)
        VALUES (?, ?, ?, ?, 'active')
    ");

    if(!$stmt){
        die("Insert Error: " . $conn->error);
    }

    $stmt->bind_param("isss", $user_id, $department, $class, $sub);
    $stmt->execute();
}

echo "<script>
alert('Subjects Assigned Successfully');
window.location.href='manage_teachers.php';
</script>";