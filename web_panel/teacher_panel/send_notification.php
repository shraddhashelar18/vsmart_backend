<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../config.php");
session_start();

/* LOGIN CHECK */
if(!isset($_SESSION['teacher_id'])){
    header("Location: ../auth_panel/login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

/* GET FROM DASHBOARD */
$class = $_GET['class'] ?? '';
$subject = $_GET['subject'] ?? '';

/* FETCH STUDENTS */
$students = [];
if($class){
    $res = $conn->query("
        SELECT user_id, full_name, roll_no, enrollment_no
        FROM students 
        WHERE class='$class'
    ");
    while($row = $res->fetch_assoc()){
        $students[] = $row;
    }
}

/* FETCH PARENTS */
$parents = [];
if($class){
    $res = $conn->query("
        SELECT p.user_id, p.full_name, s.enrollment_no
        FROM students s
        JOIN parents p ON s.enrollment_no = p.enrollment_no
        WHERE s.class='$class'
    ");
    while($row = $res->fetch_assoc()){
        $parents[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Send Notification</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>
body{margin:0;font-family:Arial;background:#f3f3f3;}
.header{background:#009846;color:white;padding:20px;display:flex;align-items:center;font-size:20px;}
.back{margin-right:10px;cursor:pointer;}
.container{padding:20px;padding-bottom:100px;}
.box{background:#eee;padding:15px;border-radius:12px;margin-bottom:15px;}
textarea{width:100%;height:100px;padding:10px;border:none;border-radius:10px;background:#f1f1f1;}
select{width:100%;padding:10px;border:none;border-radius:10px;background:#f1f1f1;}
.student{background:white;padding:15px;border-radius:12px;margin-bottom:10px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 6px rgba(0,0,0,0.1);}
.submit{
width:100%;
background:#009846;
color:white;
padding:15px;
border:none;
font-size:16px;
border-radius:10px;
margin-top:20px;
}select {
    appearance: none;
    background: #f1f1f1 url('data:image/svg+xml;utf8,<svg fill="black" height="20" viewBox="0 0 24 24" width="20"><path d="M7 10l5 5 5-5z"/></svg>') no-repeat right 10px center;
}
</style>

</head>

<body>

<div class="header">
<span class="material-icons back" onclick="history.back()">arrow_back</span>
Notify <?= $class ?> - <?= $subject ?>
</div>

<div class="container">


<form method="POST">

<div class="box">
<b>Message</b><br><br>
<textarea name="message" required></textarea>
</div>

<div class="box">
<b>Send To</b><br><br>
<select name="send_to" id="sendTo" onchange="toggleList()">
<option value="wholeStudents">Whole Class (Students)</option>
<option value="wholeParents">Whole Class (Parents)</option>
<option value="selectedStudents" selected>Selected Students</option>
<option value="selectedParents">Selected Parents</option>
</select>
</div>

<!-- STUDENTS -->
<div id="studentList">
<?php foreach($students as $s){ ?>
<div class="student">
<div>
<b><?= $s['full_name'] ?></b><br>
<small>Roll No: <?= $s['roll_no'] ?></small>
</div>
<input type="checkbox" name="students[]" value="<?= $s['user_id'] ?>">
</div>
<?php } ?>
</div>

<!-- PARENTS -->
<div id="parentList" style="display:none;">
<?php foreach($parents as $p){ ?>
<div class="student">
<div>
<b><?= $p['full_name'] ?></b><br>
<small>Enrollment No: <?= $p['enrollment_no'] ?></small>
</div>
<input type="checkbox" name="students[]" value="<?= $p['user_id'] ?>">
</div>
<?php } ?>
</div>

<button class="submit" name="send">Send</button>

</form>
</div>

<script>
function toggleList(){
    let val = document.getElementById("sendTo").value;

    let studentList = document.getElementById("studentList");
    let parentList = document.getElementById("parentList");

    if(val === "selectedStudents"){
        studentList.style.display = "block";
        parentList.style.display = "none";
    }
    else if(val === "selectedParents"){
        studentList.style.display = "none";
        parentList.style.display = "block";
    }
    else{
        studentList.style.display = "none";
        parentList.style.display = "none";
    }
}

window.onload = toggleList;
</script>

</body>
</html>

<?php

if(isset($_POST['send'])){

    $message = trim($_POST['message']);
    $sendTo = $_POST['send_to'];
    $selected = $_POST['students'] ?? [];

    $created_at = date("Y-m-d H:i:s");

    $stmt = $conn->prepare("
    INSERT INTO notifications
    (teacher_user_id, class, subject, message, created_at)
    VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issss", $teacher_id, $class, $subject, $message, $created_at);
    $stmt->execute();

    $notificationId = $conn->insert_id;

    function insertReceiver($conn,$nid,$uid){
        $stmt=$conn->prepare("INSERT INTO notification_receivers (notification_id,receiver_user_id) VALUES (?,?)");
        $stmt->bind_param("ii",$nid,$uid);
        $stmt->execute();
    }

    if($sendTo=="wholeStudents"){
        $res=$conn->query("SELECT user_id FROM students WHERE class='$class'");
        while($r=$res->fetch_assoc()) insertReceiver($conn,$notificationId,$r['user_id']);
    }
    elseif($sendTo=="selectedStudents"){
        foreach($selected as $id) insertReceiver($conn,$notificationId,$id);
    }
    elseif($sendTo=="wholeParents"){
        $res=$conn->query("SELECT DISTINCT p.user_id FROM students s JOIN parents p ON s.enrollment_no=p.enrollment_no WHERE s.class='$class'");
        while($r=$res->fetch_assoc()) insertReceiver($conn,$notificationId,$r['user_id']);
    }
    elseif($sendTo=="selectedParents"){
        foreach($selected as $id) insertReceiver($conn,$notificationId,$id);
    }

    echo "<script>alert('Notification sent');window.location='teacher_dashboard.php';</script>";
}
?>