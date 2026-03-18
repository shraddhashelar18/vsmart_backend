<?php
require_once("../config.php");

$phone=$_GET['phone'];

$stmt=$conn->prepare("
SELECT 
p.full_name,
p.mobile_no,
p.enrollment_no,
u.email
FROM parents p
LEFT JOIN users u ON p.user_id=u.user_id
WHERE p.mobile_no=?
");

$stmt->bind_param("s",$phone);
$stmt->execute();

$res=$stmt->get_result();
$parent=$res->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>

<title>Edit Parent</title>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>

body{
margin:0;
font-family:Segoe UI;
background:#f4f6f9;
}

/* HEADER */

.topbar{
background:#009846;
color:white;
padding:20px 30px;
font-size:22px;
}

.back{
color:white;
text-decoration:none;
font-size:24px;
margin-right:10px;
}

/* FORM CARD */

.wrapper{
max-width:650px;
margin:120px auto;
background:white;
padding:45px;
border-radius:18px;
box-shadow:0 8px 18px rgba(0,0,0,0.08);
}

/* INPUT WITH ICON */

.field{
display:flex;
align-items:center;
border:1px solid #ddd;
border-radius:12px;
padding:12px;
margin-bottom:18px;
background:#f2f2f2;
}

.field span{
color:#777;
margin-right:10px;
}

.field input{
border:none;
background:none;
width:100%;
font-size:15px;
outline:none;
}

/* BUTTON */

.save{
width:100%;
padding:14px;
background:#009846;
border:none;
border-radius:12px;
color:white;
font-size:16px;
cursor:pointer;
}

</style>

</head>

<body>

<div class="topbar">

<a href="javascript:history.back()" class="back">
<span class="material-icons">arrow_back</span>
</a>

Edit Parent

</div>

<div class="wrapper">

<form method="POST" action="update_parent.php">

<input type="hidden" name="old_phone" value="<?=$parent['mobile_no']?>">

<!-- NAME -->

<div class="field">

<span class="material-icons">person</span>

<input
name="name"
value="<?=$parent['full_name']?>"
placeholder="Parent Name"
required
pattern="[A-Za-z ]+"
title="Only letters and spaces allowed">

</div>

<!-- EMAIL -->

<div class="field">

<span class="material-icons">email</span>

<input
value="<?=$parent['email']?>"
readonly>

</div>

<!-- PHONE -->

<div class="field">

<span class="material-icons">phone</span>

<input
name="phone"
value="<?=$parent['mobile_no']?>"
required
pattern="[0-9]{10}"
title="Enter a valid 10 digit phone number">

</div>

<!-- ENROLLMENT -->

<div class="field">

<span class="material-icons">badge</span>

<input
value="<?=$parent['enrollment_no']?>"
readonly>

</div>

<button class="save">

Update Parent

</button>

</form>

</div>

</body>
</html>