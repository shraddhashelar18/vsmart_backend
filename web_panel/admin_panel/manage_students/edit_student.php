<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../../config.php");

$id = $_GET['id'];

/* FETCH DATA */
$data = $conn->query("
SELECT s.*,u.email
FROM students s
JOIN users u ON u.user_id=s.user_id
WHERE s.user_id='$id'
")->fetch_assoc();

/* =========================
   UPDATE LOGIC (DIRECT DB)
========================= */
if(isset($_POST['user_id'])){

    $user_id = $_POST['user_id'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $parentPhone = $_POST['parentPhone'];
    $roll = $_POST['roll'];

    /* UPDATE STUDENT TABLE */
    $stmt = $conn->prepare("
    UPDATE students
    SET full_name=?, mobile_no=?, parent_mobile_no=?, roll_no=?
    WHERE user_id=?
    ");

    $stmt->bind_param("ssssi", $name, $phone, $parentPhone, $roll, $user_id);
    $stmt->execute();

    /* REDIRECT */
    header("Location: manage_students.php?class=".$data['class']."&updated=1");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Student</title>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>
body{
margin:0;
background:#f5f7f9;
font-family:Segoe UI;
}

.readonly{
background:#e0e0e0 !important;
color:#888;
}

.topbar{
background:#009846;
color:white;
padding:20px;
font-size:22px;
display:flex;
align-items:center;
gap:10px;
}

.back{
color:white;
text-decoration:none;
font-size:26px;
}

.container{
display:flex;
justify-content:center;
margin-top:70px;
}

.form-box{
background:white;
padding:40px;
border-radius:22px;
width:520px;
box-shadow:0 10px 25px rgba(0,0,0,0.1);
}

.input-group{
display:flex;
align-items:center;
border:1px solid #ccc;
border-radius:16px;
padding:16px;
margin-bottom:18px;
background:#f8f8f8;
}

.input-group span{
margin-right:12px;
color:#666;
font-size:22px;
}

.input-group input{
border:none;
outline:none;
background:transparent;
width:100%;
font-size:17px;
}

.btn{
width:100%;
background:#009846;
color:white;
border:none;
padding:16px;
font-size:18px;
border-radius:35px;
cursor:pointer;
}
</style>
</head>

<body>

<div class="topbar">
<a href="manage_students.php?class=<?=$data['class']?>" class="back">
<span class="material-icons">arrow_back</span>
</a>
Edit Student
</div>

<div class="container">
<div class="form-box">

<!-- ✅ FORM NOW DIRECT -->
<form method="POST">

<input type="hidden" name="user_id" value="<?=$data['user_id']?>">

<div class="input-group">
<span class="material-icons">person</span>
<input name="name" value="<?=$data['full_name']?>" required>
</div>

<div class="input-group readonly">
<span class="material-icons">mail</span>
<input value="<?=$data['email']?>" readonly>
</div>

<div class="input-group">
<span class="material-icons">call</span>
<input name="phone" value="<?=$data['mobile_no']?>" maxlength="10">
</div>

<div class="input-group">
<span class="material-icons">call</span>
<input name="parentPhone" value="<?=$data['parent_mobile_no']?>" maxlength="10">
</div>

<div class="input-group">
<span class="material-icons">badge</span>
<input name="roll" value="<?=$data['roll_no']?>">
</div>

<div class="input-group readonly">
<span class="material-icons">tag</span>
<input value="<?=$data['enrollment_no']?>" readonly>
</div>

<div class="input-group readonly">
<span class="material-icons">school</span>
<input value="<?=$data['class']?>" readonly>
</div>

<button class="btn" type="submit">Update Student</button>

</form>

</div>
</div>

</body>
</html>