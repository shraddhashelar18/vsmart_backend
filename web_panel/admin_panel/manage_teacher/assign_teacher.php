<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../../config.php");

$user_id = $_GET['user_id'] ?? 0;

/* TEACHER INFO */
$t = $conn->prepare("
SELECT u.email, t.full_name 
FROM users u 
JOIN teachers t ON u.user_id = t.user_id
WHERE u.user_id=?
");

if(!$t){
    die("SQL Error: " . $conn->error);
}
$t->bind_param("i",$user_id);
$t->execute();
$teacher = $t->get_result()->fetch_assoc();

if(!$teacher){
    die("Teacher not found or not linked properly");
}  

/* DEPARTMENTS */
$dept = $conn->query("SELECT DISTINCT department FROM classes");

/* ALREADY ASSIGNED SUBJECTS */
$assigned = [];

$a = $conn->prepare("
SELECT subject 
FROM teacher_assignments 
WHERE user_id=?
");

if(!$a){
    die("SQL Error: " . $conn->error);
}

$a->bind_param("i", $user_id);
$a->execute();

$res = $a->get_result();

while($row = $res->fetch_assoc()){
    $assigned[] = $row['subject'];
}

?>

<!DOCTYPE html>
<html>
<head>

<title>Assign Teacher</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>

body{
margin:0;
font-family:Segoe UI;
background:#eef2f5;
}

.topbar {
    background: #009846;
    color: white;
    padding: 16px 20px;
    display: flex;
    align-items: center;
}

/* FIX YOUR CURRENT ISSUE */
.back-arrow {
    font-size: 24px;
    margin-right: 12px;
    cursor: pointer;
    text-decoration: none;  /* remove underline */
    color: white;           /* remove blue */
}

/* title */
.title {
    font-size: 20px;
    font-weight: 500;
}

/* CONTAINER */
.container{
max-width:600px;
margin:30px auto;
}

/* CARD */
.card{
background:white;
padding:18px;
border-radius:10px;
box-shadow:0 2px 8px rgba(0,0,0,0.08);
margin-bottom:20px;
}

/* USER INFO */
.user-info{
display:flex;
align-items:center;
gap:12px;
}

.avatar{
width:45px;
height:45px;
border-radius:50%;
background:#009846;
display:flex;
align-items:center;
justify-content:center;
color:white;
font-size:18px;
}

/* LABEL */
label{
display:block;
margin-bottom:6px;
font-weight:500;
}

/* SELECT */
select{
width:100%;
padding:12px;
border-radius:8px;
border:1px solid #ccc;
margin-bottom:15px;
font-size:14px;
}

/* BUTTON */
.btn{
width:100%;
padding:14px;
background:#009846;
color:white;
border:none;
border-radius:8px;
font-size:16px;
cursor:pointer;
transition:0.3s;
}

.btn:hover{
background:#007d38;
}

.subject {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 16px;
    margin: 10px 15px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.06);
    font-size: 15px;
}

.subject span {
    font-weight: 500;
}

.subject input[type="checkbox"] {
    width: 18px;
    height: 18px;
}

.disabled {
    opacity: 0.5;
}
</style>

</head>

<body>


<div class="header">
    <a href="user_approvals.php" class="material-icons back-arrow">arrow_back</a>
    <span class="title">Assign Teacher</span>
</div>

<div class="container">
<div class="card">
    <div class="avatar">👤</div>
    <div>
        <strong><?=$teacher['email'] ?? ''?></strong><br>
        <span style="color:#777;">Role: Teacher</span>
    </div>
</div>
<form method="POST" action="save_assignment.php">

<input type="hidden" name="user_id" value="<?=$user_id?>">

<!-- DEPARTMENT -->
<div class="select-box">
<label>Department</label>
<select id="department" name="department" required>
<option value="">Select Department</option>
<?php while($d=$dept->fetch_assoc()): ?>
<option value="<?=$d['department']?>"><?=$d['department']?></option>
<?php endwhile; ?>
</select>
</div>

<!-- CLASS -->
<div class="select-box">
<label>Class</label>
<select id="class" name="class" required>
<option value="">Select Class</option>
</select>
</div>

<!-- SUBJECTS -->
<div id="subjectsContainer"></div>

<button class="btn">Assign Subjects</button>

</form>

<script>

/* LOAD CLASSES */
document.getElementById("department").addEventListener("change", function(){

let dept = this.value;

fetch("get_classes.php?department="+dept)
.then(res=>res.json())
.then(data=>{

let classSelect = document.getElementById("class");

// clear dropdown
classSelect.innerHTML = "";

// default option
let defaultOption = document.createElement("option");
defaultOption.text = "Select Class";
defaultOption.value = "";
classSelect.appendChild(defaultOption);

// add classes
data.forEach(c=>{
let opt = document.createElement("option");
opt.value = c;
opt.text = c;
classSelect.appendChild(opt);
});

})
.catch(err => console.log(err));

});

/* LOAD SUBJECTS */
document.getElementById("class").addEventListener("change", function(){

let cls = this.value;

fetch("get_subjects.php?class="+cls)
.then(res => res.json())
.then(data => {

let subjects = data.subjects;
let assignedSubjects = data.assigned;

let container = document.getElementById("subjectsContainer");
container.innerHTML = "";

subjects.forEach(sub => {

let isAssigned = assignedSubjects.some(a => 
    a.trim().toLowerCase() === sub.trim().toLowerCase()
);

container.innerHTML += `
<div class="subject ${isAssigned ? 'disabled' : ''}">
    <span>${sub} ${isAssigned ? '(Allocated)' : ''}</span>
    <input type="checkbox"
           name="subjects[]"
           value="${sub}"
           ${isAssigned ? 'checked disabled' : ''}>
</div>
`;

});

});
});
</script>

</body>
</html>