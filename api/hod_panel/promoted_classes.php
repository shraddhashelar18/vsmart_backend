<!DOCTYPE html>
<html>
<head>

<title>Select Class</title>

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
}

.container{
padding:15px;
}

.class-link{
text-decoration:none;
color:black;
}

.class-card{
background:white;
padding:18px;
margin-bottom:15px;
border-radius:12px;
box-shadow:0 2px 6px rgba(0,0,0,0.2);
display:flex;
justify-content:space-between;
align-items:center;
font-size:18px;
}

.arrow{
font-size:20px;
}

</style>

</head>

<body>

<div class="header">
Select Class
</div>

<div class="container" id="classList"></div>

<script>

fetch("localhost/vsmart_bacckend/api/hod/get_promoted_classes.php",{

method:"POST",

headers:{
"Content-Type":"application/json"
},

body:JSON.stringify({
department:"IF"
})

})

.then(res=>res.json())
.then(data=>{

let list=document.getElementById("classList");

data.classes.forEach(cls=>{

list.innerHTML += `

<a href="promoted_students.php?class=${cls}" class="class-link">

<div class="class-card">

<div>${cls}</div>

<div class="arrow">›</div>

</div>

</a>

`;

});

});

</script>

</body>
</html>