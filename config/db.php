<?php
$conn = new mysqli("localhost", "root", "", "gym_db");
if ($conn->connect_error) {
    die("Database connection failed");
}
include "auto_expire.php";
session_start();
?>

