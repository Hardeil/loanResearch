<?php
include_once "dbConnection/dbConnection.php";
session_start();

$sql1 = "SELECT * FROM users WHERE role = 'Staff'";
$result = $conn->query($sql1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['change_user_id']) && isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
        $userId = $_SESSION['user_id'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        if ($newPassword === $confirmPassword) {
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $newPassword, $userId);
            if ($stmt->execute()) {
                echo "<script>alert('Password changed successfully.'); window.location.href = 'changePassword.php';</script>";
            } else {
                echo "<script>alert('Error changing password.');</script>";
            }
        } else {
            echo "<script>alert('Passwords do not match.');</script>";
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
    <title>Loan Request List</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container dashContainer">
        <?php include_once "component/dashNav.php";?>
        <div class="passwordForm">
                <form method="POST">
                    <input type="hidden" name="change_user_id" id="changeUserId">
                    <label>New Password:</label>
                    <input type="password" name="new_password" required>
                    <label>Confirm Password:</label>
                    <input type="password" name="confirm_password" required>
                    <button type="submit" class="submit-btn">Update Password</button>
                </form>
            </div>

    </div>
</body>
</html>
