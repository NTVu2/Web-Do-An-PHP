<?php
// db_connect.php
$host = "localhost"; // or your database host
$username = "root"; // replace with your DB username
$password = ""; // replace with your DB password
$database = "lego"; // replace with your DB name

$con = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

