<?php
$password = "vijaypatil@vsmart";

$hash = password_hash($password, PASSWORD_BCRYPT);

echo "Original Password: " . $password . "<br>";
echo "Hashed Password: " . $hash;
?>
