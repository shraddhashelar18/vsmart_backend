<?php
session_start();

/* DESTROY ALL SESSION */
session_unset();
session_destroy();

/* REDIRECT TO LOGIN */
header("Location: ../auth_panel/login.php");
exit();