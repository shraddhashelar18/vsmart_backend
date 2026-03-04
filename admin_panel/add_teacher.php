<?php
require_once "auth.php";
require_once "db.php";

$department = $_GET['dept'] ?? '';

if(empty($department)){
    die("Department not selected.");
}

/* ===============================
   GET ACTIVE SEMESTER
=================================*/
$set = $conn->query("SELECT active_semester FROM settings LIMIT 1")->fetch_assoc();
$activeSemester = $set['active_semester']; // 1=even, 0=odd


/* ===============================
   FETCH CLASSES BASED ON SEMESTER
=================================*/
if($activeSemester == 1){
    // EVEN
    $classQuery = $conn->prepare("
        SELECT class_name, semester 
        FROM classes 
        WHERE department=? AND semester % 2 = 0
        ORDER BY semester ASC
    ");
} else {
    // ODD
    $classQuery = $conn->prepare("
        SELECT class_name, semester 
        FROM classes 
        WHERE department=? AND semester % 2 != 0
        ORDER BY semester ASC
    ");
}

$classQuery->bind_param("s",$department);
$classQuery->execute();
$classResult = $classQuery->get_result();

$classList = [];
while($row = $classResult->fetch_assoc()){
    $classList[] = $row['class_name'];
}

/* ===============================
   HANDLE FORM SUBMIT
=================================*/
if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $selectedClasses = $_POST['classes'] ?? [];
    $selectedSubjects = $_POST['subjects'] ?? [];

    // Insert into users
    $stmt = $conn->prepare("INSERT INTO users (email,password,role,first_login) VALUES (?,?, 'teacher',1)");
    $stmt->bind_param("ss",$email,$password);
    $stmt->execute();
    $user_id = $stmt->insert_id;

    // Insert into teachers
    $stmt2 = $conn->prepare("INSERT INTO teachers (user_id,full_name,department) VALUES (?,?,?)");
    $stmt2->bind_param("iss",$user_id,$name,$department);
    $stmt2->execute();

    // Insert assignments
    foreach($selectedSubjects as $class => $subjects){
        foreach($subjects as $subject){

            $assign = $conn->prepare("
                INSERT INTO teacher_assignments 
                (user_id,department,class,subject,status)
                VALUES (?,?,?,?,'active')
            ");
            $assign->bind_param("isss",$user_id,$department,$class,$subject);
            $assign->execute();
        }
    }

    header("Location: manage_teachers.php?dept=".$department);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Teacher</title>
<link rel="stylesheet" href="assets/style.css">
<style>
body {background:#f5f5f5;font-family:sans-serif;}
.header {
    background:#138808;
    color:white;
    padding:20px;
    font-size:20px;
}
.container {padding:20px;}
input, select {
    width:100%;
    padding:12px;
    margin:10px 0;
    border-radius:10px;
    border:1px solid #ddd;
}
.class-box, .subject-box {
    display:inline-block;
    padding:8px 14px;
    margin:5px;
    border-radius:20px;
    border:1px solid #ccc;
    cursor:pointer;
}
.save-btn {
    background:#138808;
    color:white;
    border:none;
    padding:15px;
    width:100%;
    border-radius:10px;
    margin-top:20px;
}
.disabled-subject {
    background:#eee;
    color:#999;
    cursor:not-allowed;
}
</style>
</head>
<body>

<div class="header">
    Add Teacher
</div>

<div class="container">
<form method="POST">

<input type="text" name="name" placeholder="Enter full name" required>

<input type="email" name="email" placeholder="teacher@email.com" required>

<input type="password" name="password" placeholder="Enter password" required>

<h3>Department</h3>
<input type="text" value="<?= $department ?>" readonly>

<h3>Assign Classes</h3>

<?php foreach($classList as $class): ?>
    <div>
        <label>
            <input type="checkbox" name="classes[]" value="<?= $class ?>">
            <?= $class ?>
        </label>
    </div>

    <!-- Subjects -->
    <div style="margin-left:20px;margin-bottom:10px;">
    <?php
        $subQuery = $conn->prepare("SELECT subject_name FROM semester_subjects WHERE class=?");
        $subQuery->bind_param("s",$class);
        $subQuery->execute();
        $subRes = $subQuery->get_result();

        while($sub = $subRes->fetch_assoc()){

            $subjectName = $sub['subject_name'];

            // Check if already assigned
            $check = $conn->prepare("
                SELECT id FROM teacher_assignments 
                WHERE class=? AND subject=? AND status='active'
            ");
            $check->bind_param("ss",$class,$subjectName);
            $check->execute();
            $checkRes = $check->get_result();

            $isAssigned = $checkRes->num_rows > 0;
    ?>

        <label style="margin-right:10px;">
            <input 
                type="checkbox"
                name="subjects[<?= $class ?>][]"
                value="<?= $subjectName ?>"
                <?= $isAssigned ? 'disabled' : '' ?>
            >
            <?= $subjectName ?>
        </label>

    <?php } ?>
    </div>

<?php endforeach; ?>

<button type="submit" class="save-btn">Save Teacher</button>

</form>
</div>

</body>
</html>