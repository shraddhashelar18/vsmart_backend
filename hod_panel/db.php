<?php

$conn = new mysqli("localhost","root","","vsmart");

if($conn->connect_error){
    die("Connection Failed: ".$conn->connect_error);
}

?>