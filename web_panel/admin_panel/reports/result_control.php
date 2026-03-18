<?php
require_once("../../config.php");

/* SAVE SETTINGS */

if($_SERVER['REQUEST_METHOD']=="POST"){

    $upload = $_POST['upload'];
    $publish = $_POST['publish'];

    $conn->query("
    UPDATE settings 
    SET allow_marksheet_upload='$upload',
        final_published='$publish'
    WHERE id=1
    ");

    header("Location: result_control.php?saved=1");
    exit;
}

/* FETCH SETTINGS */

$settings=$conn->query("
SELECT allow_marksheet_upload, final_published 
FROM settings WHERE id=1
")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>

<title>Result Control</title>

<style>

body{
margin:0;
font-family:Segoe UI;
background:#f4f6f9;
}

/* TOPBAR */
.topbar{
background:#009846;
color:white;
padding:20px 30px;
font-size:22px;
}

/* DESKTOP LEFT LAYOUT */
.wrapper{
width:600px;
margin-left:80px;
margin-top:40px;
}

/* CARD */
.card{
background:white;
padding:25px;
border-radius:16px;
margin-bottom:20px;
display:flex;
justify-content:space-between;
align-items:center;
box-shadow:0 6px 15px rgba(0,0,0,0.08);
}

/* TEXT */
.card-text{
font-size:16px;
font-weight:600;
}

/* SWITCH */
.switch{
position:relative;
display:inline-block;
width:55px;
height:28px;
}

.switch input{
display:none;
}

.slider{
position:absolute;
cursor:pointer;
top:0;
left:0;
right:0;
bottom:0;
background:#ccc;
border-radius:30px;
transition:.3s;
}

.slider:before{
position:absolute;
content:"";
height:22px;
width:22px;
left:3px;
bottom:3px;
background:white;
border-radius:50%;
transition:.3s;
}

input:checked + .slider{
background:#009846;
}

input:checked + .slider:before{
transform:translateX(27px);
}

/* MESSAGE */
.msg{
padding:15px;
border-radius:12px;
margin-bottom:20px;
font-size:15px;
}

.success{
background:#e8f5ec;
color:#009846;
}

.error{
background:#fdecea;
color:red;
}

/* BUTTON */
.btn{
width:100%;
padding:16px;
background:#009846;
border:none;
color:white;
border-radius:12px;
font-size:16px;
cursor:pointer;
}

/* SAVED TOAST */
.toast{
background:#333;
color:white;
padding:12px 20px;
position:fixed;
bottom:20px;
left:50%;
transform:translateX(-50%);
border-radius:8px;
}

</style>

</head>

<body>

<div class="topbar">Result Control</div>

<div class="wrapper">

<form method="POST">

<h2 style="margin-bottom:5px;">Result Upload Settings</h2>
<p style="color:#777;margin-bottom:20px;">
Control student marksheet upload permissions.
</p>

<!-- ✅ ADD HERE -->
<div style="
background:#e6f4ea;
padding:15px;
border-radius:12px;
margin-bottom:20px;
">
ℹ Semester control is managed from Admin Settings. This screen only controls upload permissions.
</div>  


<form method="POST">
<input type="hidden" name="upload" value="0">
<input type="hidden" name="publish" value="0">
<!-- ENABLE UPLOAD -->
<div class="card">

<div class="card-text">
Enable Marksheet Upload
</div>

<label class="switch">
<input type="checkbox" name="upload" value="1"
<?=$settings['allow_marksheet_upload'] ? 'checked' : ''?>>

<input type="checkbox" name="publish" value="1"
<?=$settings['final_published'] ? 'checked' : ''?>>
<span class="slider"></span>
</label>

</div>

<!-- PUBLISH RESULT -->
<div class="card">

<div class="card-text">
Publish Final Result
</div>

<label class="switch">
<input type="checkbox" name="publish"
<?=$settings['final_published'] ? 'checked' : ''?>>
<span class="slider"></span>
</label>

</div>

<!-- STATUS MESSAGE -->

<?php if($settings['allow_marksheet_upload']): ?>

<div class="msg success">
✔ Students can upload marksheets.
</div>

<?php else: ?>

<div class="msg error">
✖ Marksheet upload is disabled.
</div>

<?php endif; ?>

<!-- SAVE BUTTON -->
<button class="btn">Save Settings</button>

</form>

</div>

<!-- TOAST -->
<?php if(isset($_GET['saved'])): ?>
<div class="toast">Result settings saved</div>
<?php endif; ?>

</body>
</html>