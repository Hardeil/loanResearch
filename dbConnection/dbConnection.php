<?php
if (isset($_SESSION['role'])) {
    header("Location: dash.php");
    exit();
}
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "loan_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
