<?php
include_once "dbConnection/dbConnection.php";
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $firstName = $conn->real_escape_string($_POST['firstName']);
    $lastName = $conn->real_escape_string($_POST['lastName']);
    $address = $conn->real_escape_string($_POST['address']);
    $contact = $conn->real_escape_string($_POST['contact']);
    $loanAmount = $conn->real_escape_string($_POST['loanAmount']);
    $duration = $conn->real_escape_string($_POST['duration']);
    $userId = $_SESSION['user_id'];
    // $dueDate = $conn->real_escape_string($_POST['dueDate']);
    $currentDate = date('Y-m-d');
    $sql = "INSERT INTO loan_list (first_name, last_name, address, contact, loan_amount, total_balance,remaining_balance,request_date, due_date, duration, pay_date,penalty_date, status, user_id)
            VALUES ('$firstName', '$lastName', '$address', '$contact', '$loanAmount', '0','0','$currentDate', 'Null' , '$duration','null','null','Pending','$userId')";

    if ($conn->query($sql) === true) {
        echo "<script>
        alert('Loan Added successfully.');
        window.location.href = 'loanList.php';
        </script>";
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$sql = "SELECT id,first_name, last_name, address, contact, loan_amount, 	total_balance, remaining_balance, due_date, status FROM loan_list WHERE status='Pending'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Loan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container dashContainer">
        <?php include_once "component/dashNav.php";?>
        <div class="tableContainer">
            <ul class="tableTitle">
                <h2>Add Loan</h2>
                <button id="openFormBtn" class="openFormBtn">Add New Loan</button>
            </ul>
            <div class="table">
                <table>
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Address</th>
                            <th>Contact</th>
                            <th>Loan Amount</th>
                            <th>Total Amount</th>
                            <th>Remaining Balance</th>
                            <th>Due Date</th>
                            <th>Request Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr style='cursor:not-allowed;'>";
        echo "<td>" . $row['first_name'] . "</td>";
        echo "<td>" . $row['last_name'] . "</td>";
        echo "<td>" . $row['address'] . "</td>";
        echo "<td>" . $row['contact'] . "</td>";
        echo "<td>P" . number_format($row['loan_amount'], 2) . "</td>";
        echo "<td>P" . number_format($row['total_balance'], 2) . "</td>";
        echo "<td>P" . number_format($row['remaining_balance'], 2) . "</td>";
        echo "<td>" . $row['due_date'] . "</td>";
        echo "<td>" . ($row['status'] === "Accept" ? "Approved" : $row['status']) . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='9' class='noData'>No records found</td></tr>";
}

?>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="form" class="popupForm">
            <div class="formContainer">
                <h2>Add New Loan</h2>
                <form action="" method="POST">
                    <label for="firstName">First Name:</label>
                    <input type="text" id="firstName" name="firstName" required>

                    <label for="lastName">Last Name:</label>
                    <input type="text" id="lastName" name="lastName" required>

                    <label for="address">Address:</label>
                    <input type="text" id="address" name="address" required>

                    <label for="contact">Contact:</label>
                    <input type="text" id="contact" name="contact" required>

                    <label for="loanAmount">Loan Amount:</label>
                    <input type="number" id="loanAmount" name="loanAmount" step="0.01" required>

                    <label for="loanDuration">Loan Duration:</label>
                    <select id="loanDuration" name="duration" required>
                        <option value="" disabled selected>Select duration</option>
                        <option value="28">1 Month</option>
                        <option value="84">3 Months</option>
                        <option value="336">1 Year</option>
                    </select>

                    <ul class="formBtn">
                        <button type="submit" class="submitBtn">Submit</button>
                        <button type="button" id="closeFormBtn" class="cancelBtn">Cancel</button>
                    </ul>
                </form>
            </div>
        </div>
    </div>
    <script src="script.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            toggleForm("openFormBtn", "closeFormBtn", "form");
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>

