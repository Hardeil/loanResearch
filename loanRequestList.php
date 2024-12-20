<?php
include_once "dbConnection/dbConnection.php";
session_start();
$sql = "SELECT ll.id, ll.client_cid, ll.first_name, ll.last_name, ll.contact, ll.loan_amount,ll.remaining_balance, ll.due_date, ll.duration, ll.status, ll.user_id, ll.birth_date,ll.gender,ll.marital_status,ll.education,ll.dosri, ll.province,u.username
        FROM loan_list ll
        INNER JOIN users u ON ll.user_id = u.id
        WHERE ll.status = 'Pending'";

$result = $conn->query($sql);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['editId'];
    $clientCid = $_POST['editClientCid'];
    $firstName = $_POST['editFirstName'];
    $lastName = $_POST['editLastName'];
    $contact = $_POST['editContact'];
    $loanAmount = $_POST['editLoanAmount'];
    $birthDate = $_POST['editBirthDate'];
    $gender = $_POST['editGender'];
    $maritalStatus = $_POST['editMaritalStatus'];
    $education = $_POST['editEducation'];
    $province = $_POST['editProvince'];
    $duration = $_POST['duration'];
    $status = isset($_POST['accept']) ? 'Accept' : 'Decline';

    $currentDate = date('Y-m-d');
    $dueDate = date('Y-m-d', strtotime($currentDate . ' + ' . $duration . ' days'));
    $payDate = date('Y-m-d', strtotime($currentDate . ' + 7 days'));
    $balance = $loanAmount * 0.03;

    if (isset($_POST['accept'])) {
        $currentDate = date('Y-m-d');
        $dueDate = date('Y-m-d', strtotime($currentDate . ' + ' . $duration . ' days'));
        $balance = $loanAmount * .03;
        $savings = $loanAmount * 0.02;
        $remainingBalance = $loanAmount + $balance + $savings;
        $sql = "UPDATE loan_list
SET client_cid=?, first_name=?, last_name=?, contact=?, loan_amount=?,
    birth_date=?, gender=?, marital_status=?, education=?, province=?,
    total_balance=?, remaining_balance=?, due_date=?, pay_date=?, penalty_date=?,
    status=?
WHERE id=?
";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssdsssssddssssd",
            $clientCid, $firstName, $lastName, $contact, $loanAmount,
            $birthDate, $gender, $maritalStatus, $education, $province,
            $remainingBalance, $remainingBalance, $dueDate, $payDate, $payDate,
            $status, $id
        );
        $sql1 = "INSERT INTO savings_tbl
        (first_name, last_name, total_balance, date, created_at)
        VALUES
        ('$firstName', '$lastName', '0' ,'$currentDate',' $currentDate')";
        $conn->query($sql1);
        if ($stmt->execute()) {
            echo "<script>
            alert('Loan accepted successfully.');
            window.location.href = 'loanRequestList.php';
            </script>";
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
            echo "<script>
            alert('Loan decline successfully.');
            window.location.href = 'loanRequestList.php';
            </script>";
            exit;
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
                            <th>First Name</th>
                            <th>Last Name</th>
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
        echo "<tr class='editableRow'
        data-id='" . $row['id'] . "'
        data-cid='" . $row['client_cid'] . "'
        data-firstname='" . $row['first_name'] . "'
        data-lastname='" . $row['last_name'] . "'
        data-contact='" . $row['contact'] . "'
        data-loanamount='" . $row['loan_amount'] . "'
        data-duration='" . $row['duration'] . "'
        data-birthdate='" . $row['birth_date'] . "'
        data-gender='" . $row['gender'] . "'
        data-maritalstatus='" . $row['marital_status'] . "'
        data-education='" . $row['education'] . "'
        data-province='" . $row['province'] . "'>";

        echo "<td>" . $row['first_name'] . "</td>";
        echo "<td>" . $row['last_name'] . "</td>";
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
            <h2>Edit Loan</h2>
            <button type="button" id="declineBtn" class="cancelBtn">X</button>
        </ul>
        <form id="form" method="POST">
            <input type="hidden" id="editId" name="editId">
            <label for="editFirstName">Client ID:</label>
            <input type="text" id="editClientCid" name="editClientCid" required>

            <label for="editFirstName">First Name:</label>
            <input type="text" id="editFirstName" name="editFirstName" required>

            <label for="editLastName">Last Name:</label>
            <input type="text" id="editLastName" name="editLastName" required>

            <label for="editContact">Contact:</label>
            <input type="text" id="editContact" name="editContact" required>

            <label for="editLoanAmount">Loan Amount:</label>
            <input type="number" id="editLoanAmount" name="editLoanAmount" step="0.01" required>

            <label for="editBirthDate">Birth Date:</label>
            <input type="date" id="editBirthDate" name="editBirthDate" required>

            <label for="editGender">Gender:</label>
            <select id="editGender" name="editGender" required>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>

            <label for="editMaritalStatus">Marital Status:</label>
            <select id="editMaritalStatus" name="editMaritalStatus" required>
                <option value="Single">Single</option>
                <option value="Married">Married</option>
                <option value="Divorced">Divorced</option>
            </select>

            <label for="editEducation">Education:</label>
            <input type="text" id="editEducation" name="editEducation" required>

            <label for="editProvince">Province:</label>
            <input type="text" id="editProvince" name="editProvince" required>

            <input type="hidden" id="duration" name="duration">

            <ul class="formBtn">
                <button type="submit" name="accept" class="submitBtn">Accept</button>
                <button type="submit" name="decline" class="declineBtn">Decline</button>
            </ul>
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
    const loanEditForm = document.getElementById('loanEditForm');
    const declineBtn = document.getElementById('declineBtn');

    rows.forEach(row => {
    row.addEventListener('click', function() {
        document.getElementById('editId').value = this.dataset.id;
        document.getElementById('editFirstName').value = this.dataset.firstname;
        document.getElementById('editLastName').value = this.dataset.lastname;
        document.getElementById('editContact').value = this.dataset.contact;
        document.getElementById('editLoanAmount').value = this.dataset.loanamount;
        document.getElementById('duration').value = this.dataset.duration;
        document.getElementById('editBirthDate').value = this.dataset.birthdate;
        document.getElementById('editGender').value = this.dataset.gender;
        document.getElementById('editMaritalStatus').value = this.dataset.maritalstatus;
        document.getElementById('editEducation').value = this.dataset.education;
        document.getElementById('editProvince').value = this.dataset.province;
        document.getElementById('editClientCid').value = this.dataset.cid;

        editForm.classList.add('active');
    });
});



    declineBtn.addEventListener('click', function() {
        // Hide the edit form when decline button is clicked
        editForm.classList.remove('active');
    });
</script>
