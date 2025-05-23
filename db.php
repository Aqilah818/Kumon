<?php
$servername = "localhost";
$username = "root";  // Your MySQL username
$password = "";      // Your MySQL password
$dbname = "kumon";     // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function redirect($url = null)
{
    //header('Location: ' . $url); 
    echo "<script>";
    echo "window.location.href='" . $url . "';";
    echo "</script>";
    exit;
}

function alert($msg = null)
{
    echo "<script>";
    echo "alert('" . $msg . "');";
    echo "</script>";
}
