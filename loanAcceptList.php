<?php
include_once "dbConnection/dbConnection.php";
session_start();
$sql = "SELECT ll.id, ll.client_cid, ll.first_name, ll.last_name, ll.contact, ll.loan_amount,ll.remaining_balance, ll.due_date, ll.duration, ll.status, ll.user_id, ll.birth_date,ll.gender,ll.marital_status,ll.education,ll.dosri, ll.province,u.username
        FROM loan_list ll
        INNER JOIN users u ON ll.user_id = u.id
        WHERE ll.status = 'Accept'";

$result = $conn->query($sql);
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
            <h2>View User Loan</h2>
            <button type="button" id="declineBtn" class="cancelBtn">X</button>
        </ul>
        <form id="form" method="POST">
            <input type="hidden" id="editId" name="editId">
            <label for="editFirstName">Client ID:</label>
            <input type="text" id="editClientCid" name="editClientCid" disabled>

            <label for="editFirstName">First Name:</label>
            <input type="text" id="editFirstName" name="editFirstName" disabled>

            <label for="editLastName">Last Name:</label>
            <input type="text" id="editLastName" name="editLastName" disabled>

            <label for="editContact">Contact:</label>
            <input type="text" id="editContact" name="editContact" disabled>

            <label for="editLoanAmount">Loan Amount:</label>
            <input type="number" id="editLoanAmount" name="editLoanAmount" step="0.01" disabled>

            <label for="editBirthDate">Birth Date:</label>
            <input type="date" id="editBirthDate" name="editBirthDate" disabled>

            <label for="editGender">Gender:</label>
            <select id="editGender" name="editGender" disabled>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>

            <label for="editMaritalStatus">Marital Status:</label>
            <select id="editMaritalStatus" name="editMaritalStatus" disabled>
                <option value="Single">Single</option>
                <option value="Married">Married</option>
                <option value="Divorced">Divorced</option>
            </select>

            <label for="editEducation">Education:</label>
            <input type="text" id="editEducation" name="editEducation" disabled>

            <label for="editProvince">Province:</label>
            <input type="text" id="editProvince" name="editProvince" disabled>

            <input type="hidden" id="duration" name="duration">
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
        editForm.classList.remove('active');
    });
</script>
