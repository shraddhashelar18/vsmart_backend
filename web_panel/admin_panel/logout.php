<?php
session_start();

/* CLEAR SESSION */
$_SESSION = [];
session_destroy();

/* OPTIONAL COOKIE CLEAN */
if(isset($_COOKIE[session_name()])){
    setcookie(session_name(), '', time()-3600, '/');
}

/* REDIRECT (OR REMOVE IF YOU DON'T WANT) */
header("Location: ../auth_panel/login.php");
exit;
?>