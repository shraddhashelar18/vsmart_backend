<?php
require_once("../auth.php");
require_once("../db.php");

if($_SERVER['REQUEST_METHOD']=="POST"){

$upload=$_POST['upload'] ?? 0;

$conn->query("
UPDATE settings
SET allow_marksheet_upload='$upload'
WHERE id=1
");

}

$settings=$conn->query("SELECT allow_marksheet_upload FROM settings WHERE id=1")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>

<title>Result Control</title>

<style>

body{
font-family:Segoe UI;
background:#f4f6f9;
margin:0;
}

.topbar{
background:#009846;
color:white;
padding:22px;
font-size:22px;
}

.wrapper{
max-width:600px;
margin:40px auto;
}

.card{
background:white;
padding:25px;
border-radius:16px;
box-shadow:0 6px 15px rgba(0,0,0,0.08);
}

button{
width:100%;
padding:14px;
background:#009846;
border:none;
color:white;
border-radius:10px;
font-size:16px;
margin-top:20px;
}

</style>

</head>

<body>

<div class="topbar">Result Control</div>

<div class="wrapper">

<form method="POST">

<div class="card">

<label>

<input type="checkbox" name="upload" value="1"
<?=$settings['allow_marksheet_upload']?'checked':''?>>

Enable Marksheet Upload

</label>

<button>Save Settings</button>

</div>

</form>

</div>

</body>
</html>