<?php
session_start();

/* Unset all session variables */
$_SESSION = array();

/* Destroy session */
session_destroy();

/* Prevent browser back button access */
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

/* Redirect to login */
header("Location: login.php");
exit;
?>