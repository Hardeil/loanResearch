<?php
include_once "dbConnection/dbConnection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $contact = $conn->real_escape_string($_POST['contact']);
    $password = $conn->real_escape_string($_POST['password']);

    // Check if the email or username is already taken
    $emailCheckSql = "SELECT * FROM users WHERE email = '$email'";
    $usernameCheckSql = "SELECT * FROM users WHERE username = '$username'";
    $emailCheckResult = $conn->query($emailCheckSql);
    $usernameCheckResult = $conn->query($usernameCheckSql);

    if ($emailCheckResult->num_rows > 0) {
        echo "<script>alert('This email is already taken. Please use a different email.');</script>";
    } elseif ($usernameCheckResult->num_rows > 0) {
        echo "<script>alert('This username is already taken. Please use a different username.');</script>";
    } else {
        $sql = "INSERT INTO users (username, email, contact, password, role, status) VALUES ('$username', '$email', '$contact', '$password', 'Staff', 'Deactivate')";

        if ($conn->query($sql) === true) {
            echo "<script>
                alert('New account created successfully!');
                window.location.href = 'index.php';
            </script>";
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./style.css">
    <title>Sign Up</title>
</head>
<body>
    <div class="container">
        <div class="navbar">
            <ul>
                <li>
                    <img src="img/b073c9e7-5ec3-43e2-b399-f2008f3b79e4-removebg-preview.png" alt="">
                </li>
                <li>
                    <h3>CARD MRI RIZAL BANK, Inc.</h3>
                </li>
            </ul>
            <a href="index.php" class="landingBtn">Sign In</a>
        </div>
        <form class="form" method="post">
            <p class="form-title">Sign up your account</p>
            <div class="input-container">
                <input type="text" placeholder="Enter username" name="username" required>
            </div>
            <div class="input-container">
                <input type="email" placeholder="Enter email" name="email" required>
            </div>
            <div class="input-container">
                <input type="number" placeholder="Enter Contact Number" name="contact" required>
            </div>
            <div class="input-container">
                <input type="password" placeholder="Enter password" name="password" required>
            </div>
            <button type="submit" class="submit">
                Sign Up
            </button>
            <p class="signup-link">
                Already have an account?
                <a href="index.php">Click Here</a>
            </p>
        </form>
    </div>
</body>
</html>
