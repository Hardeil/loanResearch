<?php
include_once "dbConnection/dbConnection.php";
session_start();

$sql1 = "SELECT id, savings_id, date, balance, type, status FROM transaction WHERE type = 'Withdrawal' AND status = 'Pending'";
$transaction = $conn->query($sql1);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $editId = $_POST['editId'];
    $action = $_POST['action'];

    if ($action === 'accept') {
        $savingsId = $_POST['editSavingsId'];
        $amount = $_POST['editbalance'];

        if (empty($editId) || empty($savingsId) || empty($amount) || $amount <= 0) {
            echo "<script>alert('Invalid request data.');</script>";
        } else {
            $sql = "UPDATE savings_tbl SET total_balance = total_balance - ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("di", $amount, $savingsId);

            if ($stmt->execute()) {
                $updateStatus = $conn->prepare("UPDATE transaction SET status = 'Approved' WHERE id = ?");
                $updateStatus->bind_param("i", $editId);

                if ($updateStatus->execute()) {
                    echo "<script>
                            alert('Withdrawal request accepted successfully.');
                            window.location.href = 'withdrawalRequestList.php';
                          </script>";
                    exit;
                } else {
                    echo "Error updating transaction status: " . $conn->error;
                }
            } else {
                echo "Error updating balance: " . $conn->error;
            }
        }
    } elseif ($action === 'decline') {
        $updateStatus = $conn->prepare("UPDATE transaction SET status = 'Declined' WHERE id = ?");
        $updateStatus->bind_param("i", $editId);

        if ($updateStatus->execute()) {
            echo "<script>
                    alert('Withdrawal request declined.');
                    window.location.href = 'withdrawalRequestList.php';
                  </script>";
            exit;
        } else {
            echo "Error updating transaction status: " . $conn->error;
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdrawal Request List</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container dashContainer">
        <?php include_once "component/dashNav.php";?>
        <div class="tableContainer">
            <ul class="tableTitle">
                <h2>Withdrawal Request</h2>
            </ul>
            <div class="table">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Full Name</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
if ($transaction->num_rows > 0) {
    while ($row = $transaction->fetch_assoc()) {
        // Fetch related savings data
        $sql = "SELECT first_name, last_name FROM savings_tbl WHERE id = " . $row['savings_id'];
        $savings = $conn->query($sql);
        $first_name = $last_name = "Unknown";

        if ($savings && $savings->num_rows > 0) {
            $savings_row = $savings->fetch_assoc();
            $first_name = $savings_row['first_name'];
            $last_name = $savings_row['last_name'];
        }

        echo "<tr class='editableRow'
                data-id='" . $row['id'] . "'
                data-savingsid='" . $row['savings_id'] . "'
                data-date='" . $row['date'] . "'
                data-balance='" . $row['balance'] . "'
                data-status='" . $row['status'] . "'>";
        echo "<td>" . date("F j, Y", strtotime($row['date'])) . "</td>";
        echo "<td>" . $first_name . " " . $last_name . "</td>";
        echo "<td>P" . number_format($row['balance'], 2) . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4' class='noData'>No records found</td></tr>";
}
?>

                    </tbody>
                </table>
            </div>
        </div>

        <!-- Edit Transaction Form -->
        <div id="editForm" class="popupForm">
            <div class="formContainer">
                <ul class="formTitle">
                    <h2>Withdrawal Form</h2>
                    <button type="button" id="declineBtn" class="cancelBtn">X</button>
                </ul>
                <h3>Do you accept the withdraw request?</h3>
                <form id="form" method="POST">
                    <input type="hidden" id="editId" name="editId">
                    <input type="hidden" id="editSavingsId" name="editSavingsId">
                    <input type="hidden" id="editbalance" name="editbalance">
                    <input type="hidden" id="action" name="action">

                    <ul class="withdrawalBtn">
                        <button type="submit" name="save" class="submitBtn" onclick="setAction('accept')">Accept</button>
                        <button type="submit" name="decline" class="cancelBtn" onclick="setAction('decline')">Decline</button>
                    </ul>
                </form>
            </div>
        </div>
    </div>

    <script>
       const rows = document.querySelectorAll('.editableRow');
const editForm = document.getElementById('editForm');
const declineBtn = document.getElementById('declineBtn');
const actionField = document.getElementById('action');

rows.forEach(row => {
    row.addEventListener('click', function () {
        document.getElementById('editId').value = this.dataset.id;
        document.getElementById('editSavingsId').value = this.dataset.savingsid;
        document.getElementById('editbalance').value = this.dataset.balance;

        editForm.classList.add('active');
    });
});

declineBtn.addEventListener('click', function () {
    editForm.classList.remove('active');
});

// Set the action field before submitting
function setAction(action) {
    actionField.value = action;
}

    </script>
</body>
</html>

<?php
$conn->close();
?>
