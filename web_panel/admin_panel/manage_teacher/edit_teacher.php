<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../../config.php");

$user_id = $_GET['id'];
$department = $_GET['department'] ?? '';

/* TEACHER */
$teacher = $conn->query("SELECT * FROM teachers WHERE user_id='$user_id'")->fetch_assoc();
$name = $teacher['full_name'];

/* EMAIL */
$user = $conn->query("SELECT email FROM users WHERE user_id='$user_id'")->fetch_assoc();
$email = $user['email'] ?? "";

/* ASSIGNED (THIS TEACHER) */
$assigned = [];
$assignedClasses = [];
$departments = [];

$res = $conn->query("
SELECT class,subject FROM teacher_assignments
WHERE user_id='$user_id' AND status='active'
");

while($row=$res->fetch_assoc()){
    $class = $row['class'];

    $assignedClasses[$class] = true;
    $assigned[$class][] = strtolower(trim($row['subject']));
    $departments[substr($class,0,2)] = true;
}

/* OTHER TEACHERS (DISABLE) */
$otherAssigned = [];

$res = $conn->query("
SELECT class, subject 
FROM teacher_assignments 
WHERE user_id != '$user_id' AND status='active'
");

while($row = $res->fetch_assoc()){
    $class = $row['class'];
    $otherAssigned[$class][] = strtolower(trim($row['subject']));
}

/* SUBJECTS */
$subjects = [];
$res = $conn->query("SELECT class,subject_name FROM semester_subjects");

while($row = $res->fetch_assoc()){
    $key = substr($row['class'], 0, 4); // FIX
    $subjects[$key][] = $row['subject_name'];
}


$allDepartments = ['IF','CO','EJ'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Teacher</title>

<style>
body{font-family:Segoe UI;background:#f4f6f9;margin:0;}
.topbar{background:#009846;color:white;padding:18px;font-size:20px;}
.wrapper{max-width:900px;margin:30px auto;}

.input{
background:#eee;
padding:12px;
border-radius:8px;
margin-bottom:15px;
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

.chip.disabled {
    background: #e0e0e0;
    color: #888;
    cursor: not-allowed;
    pointer-events: none;
}

.chip input{display:none;}

.subject-section{
display:none;
margin-top:20px;
padding-top:10px;
border-top:1px solid #ddd;
}

.save{
width:100%;
padding:14px;
background:#009846;
color:white;
border:none;
border-radius:10px;
font-size:16px;
margin-top:20px;
}
</style>
</head>

<body>

<div class="topbar">
<a href="manage_teachers.php?department=<?= $department ?>" style="color:white;text-decoration:none;">←</a>
Edit Teacher
</div>

<div class="wrapper">

<form method="POST" action="update_teacher.php">

<input type="hidden" name="user_id" value="<?= $user_id ?>">
<input type="hidden" name="department" value="<?= $department ?>">

<!-- NAME (EDITABLE) -->
<label>Teacher Name</label>
<input class="input" name="full_name" value="<?= $name ?>" required>

<!-- EMAIL (READONLY) -->
<label>Email</label>
<input class="input" value="<?= $email ?>" readonly>

<!-- DEPARTMENTS -->
<label>Departments</label><br>
<?php foreach($allDepartments as $dept): ?>
<div class="chip dept-chip <?= isset($departments[$dept])?'selected':'' ?>">
<input type="checkbox" name="departments[]" value="<?= $dept ?>" <?= isset($departments[$dept])?'checked':'' ?>>
<?= $dept ?>
</div>
<?php endforeach; ?>

<h3>Assign Classes</h3>

<?php
foreach($allDepartments as $dept):

echo "<h4>".$dept." Department</h4>";

$cls=$conn->query("SELECT class_name FROM classes WHERE department='$dept'");

while($row=$cls->fetch_assoc()):
$class=$row['class_name'];
$isSelected=isset($assignedClasses[$class]);
?>

<div class="chip class-chip <?= $isSelected?'selected':'' ?>">
<input type="checkbox" name="classes[]" value="<?= $class ?>" <?= $isSelected?'checked':'' ?>>
<?= $class ?>
</div>

<?php endwhile; endforeach; ?>

<h3>Subjects</h3>

<?php
foreach($allDepartments as $dept):

$cls=$conn->query("SELECT class_name FROM classes WHERE department='$dept'");

while($row=$cls->fetch_assoc()):
$class = $row['class_name'];
?>

<div id="subjects_<?= $class ?>" class="subject-section">

<b><?= $class ?></b><br>

<?php 
$baseClass = substr($class, 0, 4);

foreach($subjects[$baseClass] ?? [] as $sub):

    $cleanSub = strtolower(trim($sub));

    $isAssigned = in_array(
        $cleanSub,
        array_map(fn($s)=>strtolower(trim($s)), $assigned[$class] ?? [])
    );

    $isDisabled = in_array(
        $cleanSub,
        $otherAssigned[$class] ?? []
    );
?>

<div class="chip subject-chip 
<?= $isAssigned ? 'selected' : '' ?> 
<?= $isDisabled ? 'disabled' : '' ?>">

    <input type="checkbox"
        name="subjects[<?= $class ?>][]"
        value="<?= $sub ?>"
        <?= $isAssigned ? 'checked' : '' ?>
        <?= $isDisabled ? 'disabled' : '' ?>
    >

    <?= $sub ?>

</div>

<?php endforeach; ?>
</div>

<?php endwhile; endforeach; ?>

<button class="save">Update Teacher</button>

</form>

</div>

<script>

/* CLASS CLICK */
document.querySelectorAll(".class-chip").forEach(chip=>{
chip.onclick = function(){

let cb = chip.querySelector("input");
cb.checked = !cb.checked;

let className = cb.value;
let section = document.getElementById("subjects_"+className);

if(cb.checked){

chip.classList.add("selected");
if(section) section.style.display = "block";

}else{

chip.classList.remove("selected");

if(section){
section.style.display = "none";

/* clear subjects */
section.querySelectorAll("input").forEach(s=>{
if(!s.disabled){
s.checked = false;
s.closest(".chip").classList.remove("selected");
}
});
}

}

};
});

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

/* DEPARTMENT CLICK */
document.querySelectorAll(".dept-chip").forEach(chip=>{
chip.onclick = function(){

let cb = chip.querySelector("input");
cb.checked = !cb.checked;

chip.classList.toggle("selected");

};
});

/* PRELOAD */
window.onload = function(){

document.querySelectorAll(".class-chip input:checked").forEach(cb=>{

let section = document.getElementById("subjects_"+cb.value);
if(section) section.style.display = "block";

});

};

</script>
<script>

/* FORM VALIDATION */
document.querySelector("form").addEventListener("submit", function(e){

    let classChecked = document.querySelectorAll(".class-chip input:checked");
    
    if(classChecked.length === 0){
        e.preventDefault();

        alert("Please assign at least one class and subject.\nOtherwise teacher will not appear.");
        return;
    }

    let hasValidSubject = false;

    classChecked.forEach(cb=>{
        let className = cb.value;
        let section = document.getElementById("subjects_" + className);

        if(section){
            let subjects = section.querySelectorAll("input:checked:not(:disabled)");
            if(subjects.length > 0){
                hasValidSubject = true;
            }
        }
    });

    if(!hasValidSubject){
        e.preventDefault();

        alert("Each selected class must have at least one subject.\nOtherwise teacher will not appear.");
        return;
    }

});
</script>

</body>
</html>