<?php

if(!isset($_GET['class'])){
echo "Class required";
exit;
}

$class=$_GET['class'];

?>

<!DOCTYPE html>
<html>

<head>

<title><?php echo $class ?> Promoted Students</title>

<style>

body{
margin:0;
font-family:Arial;
background:#e9e4ea;
}

.header{
background:#0a8f3c;
color:white;
padding:18px;
font-size:22px;
display:flex;
align-items:center;
}

.back{
margin-right:15px;
cursor:pointer;
}

.container{
padding:15px;
}

.student-card{
background:white;
padding:15px;
margin-bottom:14px;
border-radius:12px;
box-shadow:0 2px 6px rgba(0,0,0,0.2);
}

.name{
font-size:18px;
font-weight:500;
}

.info{
font-size:14px;
color:#666;
margin-top:4px;
}

.status{
margin-top:6px;
font-weight:bold;
color:#0a8f3c;
}

</style>

</head>

<body>

<div class="header">
<span class="back" onclick="history.back()">←</span>
<?php echo $class ?> Promoted
</div>

<div class="container" id="studentList"></div>

<script>

fetch("../api/get_promoted_student.php",{

method:"POST",

headers:{
"Content-Type":"application/json"
},

body:JSON.stringify({
class:"<?php echo $class ?>"
})

})

.then(res=>res.json())
.then(data=>{

let container=document.getElementById("studentList");

data.students.forEach(st=>{

container.innerHTML+=`

<div class="student-card">

<div class="name">${st.name}</div>

<div class="info">
${st.oldClass} → ${st.newClass}
</div>

<div class="info">
Semester ${st.oldSemester} → ${st.newSemester}
</div>

<div class="status">
${st.promotionStatus}
</div>

</div>

`;

});

});

</script>

</body>
</html>