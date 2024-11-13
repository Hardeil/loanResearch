<?php
include_once "dbConnection/dbConnection.php";
session_start();
$sql = "SELECT ll.id, ll.first_name, ll.last_name, ll.address, ll.contact, ll.loan_amount,
               ll.remaining_balance, ll.due_date, ll.duration, ll.status, ll.user_id, u.username
        FROM loan_list ll
        INNER JOIN users u ON ll.user_id = u.id
        WHERE ll.status = 'Accept'";
$result = $conn->query($sql);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['editId'];
    $firstName = $_POST['editFirstName'];
    $lastName = $_POST['editLastName'];
    $address = $_POST['editAddress'];
    $contact = $_POST['editContact'];
    $loanAmount = $_POST['editLoanAmount'];
    $duration = $_POST['duration'];
    $status = 'Accept';

    if (isset($_POST['accept'])) {
        $currentDate = date('Y-m-d');
        $dueDate = date('Y-m-d', strtotime($currentDate . ' + ' . $duration . ' days'));
        $balance = $loanAmount * .03;
        $remainingBalance = $loanAmount + $balance;
        $sql = "UPDATE loan_list SET first_name=?, last_name=?, address=?, contact=?, loan_amount=?,total_balance=?, remaining_balance=?, due_date=?, status=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssdddssi", $firstName, $lastName, $address, $contact, $loanAmount, $remainingBalance, $remainingBalance, $dueDate, $status, $id);

        if ($stmt->execute()) {
            echo "<script>alert('Loan accepted successfully.');</script>";
            header("Location: loanRequestList.php");
            exit;
        } else {
            echo "<script>alert('Error accepting loan.');</script>";
        }
    } elseif (isset($_POST['decline'])) {
        $status = 'Decline';

        $sql = "UPDATE loan_list SET status=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $id);

        if ($stmt->execute()) {
            echo "<script>alert('Loan declined successfully.');</script>";
        } else {
            echo "<script>alert('Error declining loan.');</script>";
        }
    }

    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Accept List</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container dashContainer">
        <?php include_once "component/dashNav.php";?>
        <div class="tableContainer">
            <ul class="tableTitle">
                <h2>Loan Accept List</h2>
            </ul>
            <div class="table">
            <table >
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Address</th>
                        <th>Contact</th>
                        <th>Loan Amount</th>
                        <th>Remaining Balance</th>
                        <th>Due Date</th>
                        <th>Duration</th>
                        <th>Staff Added</th>
                    </tr>
                </thead>
                <tbody>
                <?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr class='editableRow' data-id='" . $row['id'] . "' data-firstname='" . $row['first_name'] . "' data-lastname='" . $row['last_name'] . "' data-address='" . $row['address'] . "' data-contact='" . $row['contact'] . "' data-loanamount='" . $row['loan_amount'] . "' data-remainingbalance='" . $row['remaining_balance'] . "' data-duedate='" . $row['due_date'] . "' data-duration='" . $row['duration'] . "'>";
        echo "<td>" . $row['first_name'] . "</td>";
        echo "<td>" . $row['last_name'] . "</td>";
        echo "<td>" . $row['address'] . "</td>";
        echo "<td>" . $row['contact'] . "</td>";
        echo "<td>P" . number_format($row['loan_amount'], 2) . "</td>";
        echo "<td>P" . number_format($row['remaining_balance'], 2) . "</td>";
        echo "<td>" . $row['due_date'] . "</td>";
        echo "<td>" . $row['duration'] . " days" . "</td>";
        echo "<td>" . $row['username'] . "</td>";
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

        <!-- Edit Loan Form -->
        <div id="editForm" class="popupForm">
            <div class="formContainer">
                <ul class="formTitle">
                    <h2>View User Loan</h2>
                    <button type="button" id="declineBtn" class="cancelBtn">X</button>
                </ul>
                <form id="form" method="POST">
                    <input type="hidden" id="editId" name="editId">
                    <label for="editFirstName">First Name:</label>
                    <input type="text" id="editFirstName" name="editFirstName" readonly>

                    <label for="editLastName">Last Name:</label>
                    <input type="text" id="editLastName" name="editLastName" readonly>

                    <label for="editAddress">Address:</label>
                    <input type="text" id="editAddress" name="editAddress" readonly>

                    <label for="editContact">Contact:</label>
                    <input type="text" id="editContact" name="editContact" readonly>

                    <label for="editLoanAmount">Loan Amount:</label>
                    <input type="number" id="editLoanAmount" name="editLoanAmount" step="0.01" readonly>

                    <label for="editRemainingBalance">Remaining Balance:</label>
                    <input type="number" id="editRemainingBalance" name="editRemainingBalance" step="0.01" readonly>


                    <!-- <ul class="formBtn">
                        <button type="submit" name="accept" class="submitBtn">Accept</button>
                        <button type="submit" name="decline" class="declineBtn">Decline</button>
                    </ul> -->
                </form>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
<script>
const rows = document.querySelectorAll('.editableRow');
const editForm = document.getElementById('editForm');
const declineBtn = document.getElementById('declineBtn');

rows.forEach(row => {
    row.addEventListener('click', function() {
        document.getElementById('editId').value = this.dataset.id;
        document.getElementById('editFirstName').value = this.dataset.firstname;
        document.getElementById('editLastName').value = this.dataset.lastname;
        document.getElementById('editAddress').value = this.dataset.address;
        document.getElementById('editContact').value = this.dataset.contact;
        document.getElementById('editLoanAmount').value = this.dataset.loanamount;
        document.getElementById('editRemainingBalance').value = this.dataset.remainingbalance;

        editForm.classList.add('active');
    });
});

declineBtn.addEventListener('click', function() {
    // Hide the edit form when the decline button is clicked
    editForm.classList.remove('active');
});

</script>
