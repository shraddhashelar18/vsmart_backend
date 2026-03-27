<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../../config.php");

if(!isset($_GET['department'])){
    die("Department missing");
}

$department = $_GET['department'];

/* GET CLASSES */
$classes = $conn->query("
SELECT class_name
FROM classes
WHERE department='$department'
");

/* GET SUBJECTS */
$subjects = [];

$res = $conn->query("
SELECT class, subject_name
FROM semester_subjects
");

while($row = $res->fetch_assoc()){
    $baseClass = substr($row['class'],0,4);
    $subjects[$baseClass][] = $row['subject_name'];
}

/* GET ASSIGNED */
$assigned = [];

$res = $conn->query("
SELECT class, subject
FROM teacher_assignments
WHERE status='active'
");

while($row = $res->fetch_assoc()){
    $assigned[$row['class']][] = $row['subject'];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Teacher</title>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>

body{font-family:Segoe UI;background:#f4f6f9;margin:0;}

.topbar{
background:#009846;
color:white;
padding:18px 25px;
font-size:22px;
display:flex;
align-items:center;
gap:10px;
}

.wrapper{
max-width:900px;
margin:30px auto;
}

.input{
background:#eee;
padding:14px;
border-radius:10px;
margin-bottom:18px;
border:none;
width:100%;
}

.chip{
display:inline-block;
padding:10px 18px;
border-radius:20px;
border:1px solid #ccc;
margin:6px;
cursor:pointer;
background:white;
}

.chip.selected{
background:#e6dcff;
border-color:#c3b5ff;
color:#5a4fcf;
}

.chip.disabled{
background:#e0e0e0;
color:#888;
pointer-events:none;
}

.chip input{
display:none;
}

.subject-section{
display:none;
margin-top:20px;
padding-top:10px;
border-top:1px solid #ddd;
}

</style>
</head>

<body>

<div class="topbar">
<a href="manage_teachers.php?department=<?= $department ?>" style="color:white;text-decoration:none;">
<span class="material-icons">arrow_back</span>
</a>
Add Teacher
</div>

<div class="wrapper">

<form method="POST" action="save_teacher.php">

<!-- Name -->
<label>Name</label>
<input class="input" name="name" placeholder="Enter full name" required>

<!-- Email -->
<label>Email</label>
<input class="input" type="email" name="email" placeholder="teacher@vpt.edu.in" required>

<!-- Password -->
<label>Password</label>
<input class="input" type="password" name="password" placeholder="Enter password" required>

<!-- DEPARTMENT -->
<label>Department</label><br>
<div class="chip selected">✔ <?= $department ?></div>
<input type="hidden" name="department" value="<?= $department ?>">

<h3>Assign Classes</h3>

<?php while($row=$classes->fetch_assoc()):
$class = $row['class_name'];
?>

<!-- FIXED (div instead of label) -->
<div class="chip class-chip" onclick="toggleClass(this)">
    <input type="checkbox" name="classes[]" value="<?= $class ?>">
    <?= $class ?>
</div>

<?php endwhile; ?>

<h3>Subjects</h3>

<?php
$classes->data_seek(0);
while($row=$classes->fetch_assoc()):
$class = $row['class_name'];
$base = substr($class,0,4);
?>

<div id="subjects_<?= $class ?>" class="subject-section">

<b><?= $class ?></b><br>

<?php foreach($subjects[$base] ?? [] as $sub):

$isAssigned = in_array(
    strtolower(trim($sub)),
    array_map(fn($s)=>strtolower(trim($s)), $assigned[$class] ?? [])
);
?>

<!-- FIXED (div instead of label) -->
<div class="chip <?= $isAssigned ? 'disabled selected' : '' ?>">
    <input type="checkbox"
    name="subjects[<?= $class ?>][]"
    value="<?= $sub ?>"
    <?= $isAssigned ? 'checked disabled' : '' ?>>
    <?= $sub ?>
</div>

<?php endforeach; ?>

</div>

<?php endwhile; ?>

<button style="width:100%;padding:14px;background:#009846;color:white;border:none;border-radius:10px;margin-top:20px;">
Save Teacher
</button>

</form>

</div>

<script>

/* CLASS CLICK */
function toggleClass(el){

    let cb = el.querySelector("input");

    cb.checked = !cb.checked;

    let className = cb.value;
    let section = document.getElementById("subjects_"+className);

    if(cb.checked){

        el.classList.add("selected");

        if(section){
            section.style.display = "block";
        }

    }else{

        el.classList.remove("selected");

        if(section){
            section.style.display = "none";

            // clear subjects
            section.querySelectorAll("input").forEach(s=>{
                if(!s.disabled){
                    s.checked = false;
                    s.closest(".chip").classList.remove("selected");
                }
            });
        }
    }
}

/* SUBJECT CLICK */
document.addEventListener("click", function(e){

    let chip = e.target.closest(".subject-section .chip");

    if(!chip || chip.classList.contains("disabled")) return;

    let cb = chip.querySelector("input");

    cb.checked = !cb.checked;

    if(cb.checked){
        chip.classList.add("selected");
    }else{
        chip.classList.remove("selected");
    }

});

/* VALIDATION */
document.querySelector("form").onsubmit=function(){

let cls=document.querySelector("input[name='classes[]']:checked");

if(!cls){
alert("Select at least one class");
return false;
}

return true;
}

</script>

</body>
</html>