<?php
include_once "dbConnection/dbConnection.php";
session_start();

$sql1 = "SELECT * FROM users WHERE role = 'Staff'";
$result = $conn->query($sql1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['user_id']) && isset($_POST['new_status'])) {
        // Update user status
        $userId = $_POST['user_id'];
        $newStatus = $_POST['new_status'];

        $sql = "UPDATE users SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $newStatus, $userId);

        if ($stmt->execute()) {
            echo "<script>alert('User status updated successfully.');
            window.location.href = 'userList.php';</script>";
        } else {
            echo "<script>alert('Error updating user status.');</script>";
        }
    }

    if (isset($_POST['reset_user_id'])) {
        $userId = $_POST['reset_user_id'];
        $defaultPassword = "defaultPassword123";

        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $defaultPassword, $userId);

        if ($stmt->execute()) {
            echo "<script>alert('Password reset completed. Default password is now set.');
            window.location.href = 'userList.php';</script>";
        } else {
            echo "<script>alert('Error resetting password.');</script>";
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
        <div class="tableContainer">
            <ul class="tableTitle">
                <h2>Loan List</h2>
            </ul>
            <div class="table">
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['username']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>{$row['contact']}</td>";
        echo "<td>{$row['role']}</td>";
        echo "<td>{$row['status']}</td>";
        echo "<td>";
        echo "<form method='POST' style='display:inline-block;'>";
        echo "<input type='hidden' name='user_id' value='{$row['id']}'>";
        echo "<input type='hidden' name='new_status' value='" . ($row['status'] === 'Active' ? 'Inactive' : 'Active') . "'>";
        echo "<button type='submit' class='action-btn'>" . ($row['status'] === 'Active' ? 'Deactivate' : 'Activate') . "</button>";
        echo "</form>";
        echo "<form method='POST' style='display:inline-block; margin-left: 5px;'>";
        echo "<input type='hidden' name='reset_user_id' value='{$row['id']}'>";
        echo "<button type='submit' class='reset-btn'>Reset Password</button>";
        echo "</form>";

        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6' class='noData'>No records found</td></tr>";
}
?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
