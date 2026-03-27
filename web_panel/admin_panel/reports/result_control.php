<?php
require_once("../../config.php");
require_once("../helpers/promotion_helper.php");

/* SAVE SETTINGS */

if($_SERVER['REQUEST_METHOD']=="POST"){

    $upload = isset($_POST['upload']) ? 1 : 0;
    $publish = isset($_POST['publish']) ? 1 : 0;

    $stmt = $conn->prepare("
    UPDATE settings 
    SET allow_marksheet_upload=?, final_published=?
    WHERE id=1
    ");
    $stmt->bind_param("ii",$upload,$publish);
    $stmt->execute();

    /* 🚀 CALL HELPER */
    if($publish == 1){
        runPromotion($conn);
    }

    header("Location: result_control.php?saved=1");
    exit;
}

/* FETCH SETTINGS */

$settings = $conn->query("
SELECT allow_marksheet_upload, final_published 
FROM settings WHERE id=1
")->fetch_assoc();

$uploadEnabled = $settings['allow_marksheet_upload'];
$publishEnabled = $settings['final_published'];
?>

<!DOCTYPE html>
<html>
<head>

<title>Result Control</title>
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
    padding:18px 30px;
    font-size:20px;
    display:flex;
    align-items:center;
    gap:10px;
}

.back-btn{
    color:white;
    text-decoration:none;
    display:flex;
    align-items:center;
}

.back-btn .material-icons{
    font-size:24px;
    cursor:pointer;
}

.title-text{
    font-weight:600;
}

/* CONTAINER */
.container{
max-width:600px;
margin:40px auto;
padding:20px;
}

/* CARD */
.card{
background:white;
border-radius:14px;
padding:16px;
margin-bottom:15px;
display:flex;
align-items:center;
justify-content:space-between;
box-shadow:0 4px 12px rgba(0,0,0,0.08);
}

/* LEFT */
.left{
display:flex;
align-items:center;
gap:12px;
}

.icon{
color:#009846;
font-size:26px;
}

/* TEXT */
.title{
font-weight:600;
font-size:16px;
}

/* SWITCH */
.switch{
position:relative;
width:50px;
height:26px;
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
content:"";
position:absolute;
height:20px;
width:20px;
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
transform:translateX(24px);
}

/* DISABLED */
.disabled{
opacity:0.5;
pointer-events:none;
}

/* STATUS BOX */
.status{
padding:14px;
border-radius:12px;
margin-top:10px;
display:flex;
align-items:center;
gap:10px;
font-weight:600;
}

.success{
background:#e8f5ec;
color:#009846;
}

.error{
background:#fdecea;
color:#e53935;
}

/* BUTTON */
.btn{
width:100%;
padding:14px;
background:#009846;
color:white;
border:none;
border-radius:10px;
margin-top:20px;
cursor:pointer;
}

/* INFO */
.info{
background:#eaf7f1;
padding:12px;
border-radius:10px;
margin-top:20px;
display:flex;
gap:10px;
}

/* TOAST */
.toast{
position:fixed;
bottom:20px;
left:50%;
transform:translateX(-50%);
background:#333;
color:white;
padding:10px 20px;
border-radius:8px;
}

</style>

</head>

<body>

<div class="topbar">
    <a href="../reports.php" class="back-btn">
        <span class="material-icons">arrow_back</span>
    </a>
    <span class="title-text">Result Control</span>
</div>
<div class="container">

<h2>Result Upload Settings</h2>
<p style="color:#777;">Control student marksheet upload permissions.</p>

<form method="POST">

<!-- UPLOAD -->
<div class="card">
<div class="left">
<span class="material-icons icon">upload_file</span>
<div class="title">Enable Marksheet Upload</div>
</div>

<label class="switch">
<input type="checkbox" id="uploadToggle"
<?=$uploadEnabled ? 'checked' : ''?>>
<span class="slider"></span>
</label>
</div>

<!-- PUBLISH -->
<div class="card <?=$uploadEnabled ? '' : 'disabled'?>">

<div class="left">
<span class="material-icons icon">refresh</span>
<div class="title">Publish Final Result</div>
</div>

<label class="switch">
<input type="checkbox" id="publishToggle"
<?=$publishEnabled ? 'checked' : ''?>
<?=$uploadEnabled ? '' : 'disabled'?>>
<span class="slider"></span>
</label>

</div>

<!-- STATUS -->
<?php if($uploadEnabled): ?>

<div class="status success">
<span class="material-icons">check_circle</span>
Students can upload marksheets.
</div>

<?php else: ?>

<div class="status error">
<span class="material-icons">cancel</span>
Marksheet upload is disabled.
</div>

<?php endif; ?>

<!-- VIEW STATUS BUTTON -->
<?php if($uploadEnabled): ?>

<button type="button" class="btn view-btn" onclick="location.href='upload_status.php'">
View Upload Status
</button>

<?php endif; ?>

<!-- INFO -->
<div class="info">
<span class="material-icons">info</span>
<div>
Semester control is managed from Admin Settings. 
This screen only controls upload permissions.
</div>
</div>

<button class="btn">Save Settings</button>

</form>

</div>

<?php if(isset($_GET['saved'])): ?>
<div class="toast">Result settings saved</div>
<?php endif; ?>
<script>
const uploadToggle = document.getElementById("uploadToggle");
const publishToggle = document.getElementById("publishToggle");
const statusBox = document.querySelector(".status");
const viewBtn = document.querySelector(".view-btn");
const publishCard = publishToggle.closest(".card");

function updateDB(){
    fetch("update_result_control.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            upload: uploadToggle.checked ? 1 : 0,
            publish: publishToggle.checked ? 1 : 0
        })
    })
    .then(res => res.json())
    .then(data => {
        console.log("Updated", data);
    })
    .catch(err => {
        console.error("Error:", err);
    });
}
// upload toggle
uploadToggle.addEventListener("change", () => {

    if(!uploadToggle.checked){
        publishToggle.checked = false;
        publishToggle.disabled = true;
        publishCard.classList.add("disabled");
    } else {
        publishToggle.disabled = false;
        publishCard.classList.remove("disabled");
    }

    updateDB();
});

// publish toggle
publishToggle.addEventListener("change", updateDB);
</script>
</body>
</html>