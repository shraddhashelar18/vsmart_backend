<?php
require_once("../auth.php");
require_once("../db.php");

if(!isset($_GET['class'])){
    echo "Class not selected";
    exit;
}

$class=$_GET['class'];

$stmt=$conn->prepare("
SELECT s.full_name,s.mobile_no,s.class,u.email,u.user_id
FROM students s
JOIN users u ON s.user_id=u.user_id
WHERE s.class=?
");

$stmt->bind_param("s",$class);
$stmt->execute();
$result=$stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>

<title>Manage Students</title>

<link rel="stylesheet" href="/vsmart/admin_panel/assets/style.css">

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>

.search-box{
width:100%;
padding:15px;
border:none;
border-radius:12px;
background:#f1f3f5;
margin-top:20px;
font-size:15px;
}

.student-card{
background:#f5f5f5;
border-radius:14px;
padding:18px;
margin-top:15px;
display:flex;
justify-content:space-between;
align-items:center;
}

.student-left{
display:flex;
align-items:center;
}

.avatar{
width:42px;
height:42px;
border-radius:50%;
background:#EAF7F1;
display:flex;
align-items:center;
justify-content:center;
margin-right:12px;
}

.avatar i{
color:#009846;
}

.student-info{
font-size:14px;
}

.student-info b{
font-size:16px;
}

.actions i{
font-size:22px;
margin-left:10px;
cursor:pointer;
}

.edit{
color:#2196F3;
}

.delete{
color:#F44336;
}

.add-btn{
position:fixed;
bottom:30px;
right:30px;
background:#009846;
width:60px;
height:60px;
border-radius:50%;
display:flex;
align-items:center;
justify-content:center;
color:white;
text-decoration:none;
box-shadow:0 4px 10px rgba(0,0,0,0.2);
}

</style>

</head>

<body>

<div class="header">
<h1>Manage Students - <?php echo $class; ?></h1>
</div>

<div class="container">

<input 
type="text"
id="search"
class="search-box"
placeholder="Search by name, email, phone or ID..."
onkeyup="searchStudents()"
>

<p id="count" style="color:grey;margin-top:10px;">
<?php echo $result->num_rows;?> students found
</p>

<div id="studentList">

<?php while($row=$result->fetch_assoc()){ ?>

<div class="student-card"
data-search="<?php echo strtolower($row['full_name']." ".$row['email']." ".$row['mobile_no']." ".$row['class']); ?>">

<div class="student-left">

<div class="avatar">
<i class="material-icons">person</i>
</div>

<div class="student-info">

<b><?php echo $row['full_name']; ?></b><br>

<?php echo $row['email']; ?><br>

<?php echo $row['mobile_no']; ?><br>

<?php echo $row['class']; ?>

</div>

</div>

<div class="actions">

<a href="edit_student.php?id=<?php echo $row['user_id']; ?>">
<i class="material-icons edit">edit</i>
</a>

<a href="delete_student.php?id=<?php echo $row['user_id']; ?>">
<i class="material-icons delete">delete</i>
</a>

</div>

</div>

<?php } ?>

</div>

</div>

<a href="add_student.php?class=<?php echo $class; ?>" class="add-btn">
<i class="material-icons">add</i>
</a>

<script>

function searchStudents(){

let input=document.getElementById("search").value.toLowerCase();

let cards=document.querySelectorAll(".student-card");

let count=0;

cards.forEach(card=>{

let text=card.getAttribute("data-search");

if(text.includes(input)){

card.style.display="flex";
count++;

}else{

card.style.display="none";

}

});

document.getElementById("count").innerText=count+" students found";

}

</script>

</body>
</html>