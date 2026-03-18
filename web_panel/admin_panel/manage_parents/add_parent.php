<?php
require_once("../../config.php");

$class=$_GET['class'];
?>

<!DOCTYPE html>
<html>
<head>

<title>Add Parent</title>

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

/* FORM BOX */

.wrapper{
max-width:650px;
margin:120px auto;
background:white;
padding:45px;
border-radius:18px;
box-shadow:0 8px 18px rgba(0,0,0,0.08);
}

/* FIELD WITH ICON */

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

<a href="manage_parents.php?class=<?=$class?>" class="back">
<span class="material-icons">arrow_back</span>
</a>

Add Parent

</div>

<div class="wrapper">

<form method="POST" action="save_parent.php">

<input type="hidden" name="class" value="<?=$class?>">

<!-- NAME -->

<div class="field">

<span class="material-icons">person</span>

<input
name="name"
placeholder="Parent Name"
required
pattern="[A-Za-z ]+"
title="Only letters allowed">

</div>

<!-- EMAIL -->

<div class="field">

<span class="material-icons">email</span>

<input
name="email"
placeholder="Email"
type="email"
required>

</div>

<!-- PASSWORD -->

<div class="field">

<span class="material-icons">lock</span>

<input
name="password"
placeholder="Password"
type="password"
required
minlength="6"
title="Password must be at least 6 characters">

</div>

<!-- PHONE -->

<div class="field">

<span class="material-icons">phone</span>

<input
name="phone"
placeholder="Phone Number"
required
pattern="[0-9]{10}"
title="Enter 10 digit phone number">

</div>

<!-- STUDENT ENROLLMENT -->

<div class="field">

<span class="material-icons">badge</span>

<input
name="enrollment"
placeholder="Student Enrollment"
required
pattern="[0-9]+"
title="Only numbers allowed">

</div>

<button class="save">

Save Parent

</button>

</form>

</div>

</body>
</html>