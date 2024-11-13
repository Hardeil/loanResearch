<?php
session_start();
include_once "dbConnection/dbConnection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $loginInput = $conn->real_escape_string($_POST['loginInput']);
    $password = $conn->real_escape_string($_POST['password']);

    $sql = "SELECT * FROM users WHERE (email = '$loginInput' OR username = '$loginInput') AND status = 'active'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['loggedin'] = true;

            echo "<script>
                alert('Login successful!');
                window.location.href = 'dash.php';
            </script>";
        } else {
            echo "<script>alert('Incorrect password.');</script>";
        }
    } else {
        $inactiveCheckSql = "SELECT * FROM users WHERE (email = '$loginInput' OR username = '$loginInput')";
        $inactiveResult = $conn->query($inactiveCheckSql);

        if ($inactiveResult->num_rows > 0) {
            echo "<script>alert('Your account is not active.');</script>";
        } else {
            echo "<script>alert('No account found with that email or username.');</script>";
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
    <title>Login</title>
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
            <a href="signUp.php" class="landingBtn">Sign Up</a>
        </div>
        <form class="form" method="post">
            <p class="form-title">Sign in to your account</p>
            <div class="input-container">
                <input type="text" name="loginInput" placeholder="Enter email or Username" required>
            </div>
            <div class="input-container">
                <input type="password" name="password" placeholder="Enter password" required>
            </div>
            <button type="submit" class="submit">
                Sign in
            </button>
            <p class="signup-link">
                Don't have an account?
                <a href="signUp.php">Click Here</a>
            </p>
        </form>
    </div>
</body>
</html>
