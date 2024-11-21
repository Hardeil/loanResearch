<?php
include_once "dbConnection/dbConnection.php";
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $clientCID = $conn->real_escape_string($_POST['clientCID']);
    $firstName = $conn->real_escape_string($_POST['firstName']);
    $middleName = $conn->real_escape_string($_POST['middleName']);
    $lastName = $conn->real_escape_string($_POST['lastName']);
    $centerName = $conn->real_escape_string($_POST['centerName']);
    $birthDate = $conn->real_escape_string($_POST['birthDate']);
    $contact = $conn->real_escape_string($_POST['contact']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $maritalStatus = $conn->real_escape_string($_POST['maritalStatus']);
    $education = $conn->real_escape_string($_POST['education']);
    $country = $conn->real_escape_string($_POST['country']);
    $region = $conn->real_escape_string($_POST['region']);
    $province = $conn->real_escape_string($_POST['province']);
    $zipcode = $conn->real_escape_string($_POST['zipcode']);
    $dosri = $conn->real_escape_string($_POST['dosri']);
    $loanAmount = $conn->real_escape_string($_POST['loanAmount']);
    $duration = $conn->real_escape_string($_POST['duration']);
    $userId = $_SESSION['user_id'];
    $currentDate = date('Y-m-d');

    $sql = "INSERT INTO loan_list
            (client_cid, first_name, middle_name, last_name, center_name, birth_date, contact, gender, marital_status, education, country, region, province, zip_code, dosri, loan_amount,total_balance,remaining_balance, duration, request_date, due_date,pay_date,penalty_date,status, user_id)
            VALUES
            ('$clientCID', '$firstName', '$middleName', '$lastName', '$centerName', '$birthDate', '$contact', '$gender', '$maritalStatus', '$education', '$country', '$region', '$province', '$zipcode', '$dosri', '$loanAmount',0,0 ,'$duration', '$currentDate','none','none','none', 'Pending', '$userId')";

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

$sql = "SELECT id, first_name, last_name, contact, loan_amount, total_balance, remaining_balance, due_date, status
        FROM loan_list
        WHERE status='Pending'";
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
        <h2 class="form-title">Add New Loan</h2>
        <!-- Step 1 -->
        <form method="POST">
        <div id="formStep1" class="formStep activeStep">
            <div class="input-container">
                <label for="clientCID">Client CID:</label>
                <input type="text" id="clientCID" name="clientCID" required>
            </div>
            <div class="input-container">
                <label for="firstName">First Name:</label>
                <input type="text" id="firstName" name="firstName" required>
            </div>
            <div class="input-container">
                <label for="middleName">Middle Name:</label>
                <input type="text" id="middleName" name="middleName">
            </div>
            <div class="input-container">
                <label for="lastName">Last Name:</label>
                <input type="text" id="lastName" name="lastName" required>
            </div>
            <div class="input-container">
                <label for="centerName">Center Name:</label>
                <input type="text" id="centerName" name="centerName">
            </div>
            <div class="input-container">
                <label for="birthDate">Birth Date:</label>
                <input type="date" id="birthDate" name="birthDate" required>
            </div>

            <div class="input-container">
                <label for="contactNumber">Contact Number:</label>
                <input type="text" id="contact" name="contact" required>
            </div>
            <div class="input-container">
                <label for="gender">Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="" disabled selected>Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
            <div class="input-container">
                <label for="maritalStatus">Marital Status:</label>
                <select id="maritalStatus" name="maritalStatus" required>
                    <option value="" disabled selected>Select Marital Status</option>
                    <option value="Single">Single</option>
                    <option value="Married">Married</option>
                    <option value="Divorced">Divorced</option>
                </select>
            </div>
            <div class="formBtn">
                <button type="button" id="nextStep1" class="nextBtn">Next</button>
                <button type="button" id="closeFormBtn" class="cancelBtn">Cancel</button>
            </div>
        </div>

        <div id="formStep2" class="formStep">

            <div class="input-container">
                <label for="education">Educational Attainment:</label>
                <input type="text" id="education" name="education">
            </div>

            <div class="input-container">
                <label for="country">Country:</label>
                <input type="text" id="country" name="country" required>
            </div>

            <div class="input-container">
                <label for="region">Region:</label>
                <input type="text" id="region" name="region" required>
            </div>

            <div class="input-container">
                <label for="province">Province:</label>
                <input type="text" id="province" name="province" required>
            </div>

            <div class="input-container">
                <label for="zipcode">Zip Code:</label>
                <input type="text" id="zipcode" name="zipcode" required>
            </div>

            <div class="input-container">
                <label for="dosri">DOSRI:</label>
                <input type="text" id="dosri" name="dosri">
            </div>
            <div class="input-container">
                <label for="loanAmount">Loan Amount:</label>
                <input type="number" id="loanAmount" name="loanAmount" required>
            </div>
            <label for="loanDuration">Loan Duration:</label>
                    <select id="loanDuration" name="duration" required>
                        <option value="" disabled selected>Select duration</option>
                        <option value="28">1 Month</option>
                        <option value="84">3 Months</option>
                        <option value="336">1 Year</option>
                    </select>
            <div class="formBtn">
                <button type="button" id="backStep2" class="backBtn">Back</button>
                <button type="submit" class="submit">Submit</button>
            </div>
            </form>
</div>
    </div>
</div>

</div>

    </div>
    <script src="script.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            toggleForm("openFormBtn", "closeFormBtn", "form");
        });
        document.addEventListener("DOMContentLoaded", function () {
    const formSteps = document.getElementById("formStep1");
    const formSteps2 = document.getElementById("formStep2");
    const nextStep1 = document.getElementById("nextStep1");
    const backStep2 = document.getElementById("backStep2");
    const closeFormBtn = document.getElementById("closeFormBtn");
    const form = document.getElementById("form");
    function checkRequiredFields() {
        const requiredFields = formStep1.querySelectorAll("input[required], select[required]");
        let allFilled = true;

        requiredFields.forEach((field) => {
            if (!field.value.trim()) {
                allFilled = false;
            }
        });

        nextStep1.disabled = !allFilled;
    }
    formStep1.addEventListener("input", checkRequiredFields);
    formSteps2.style.display="none";
    nextStep1.addEventListener("click", function () {
        formSteps2.style.display="block";
        formSteps.style.display="none";
    });

    backStep2.addEventListener("click", function () {
        formSteps2.style.display="none";
        formSteps.style.display="Block";
    });

    closeFormBtn.addEventListener("click", function () {
        form.classList.remove("active");
    });
    nextStep1.disabled = true;

checkRequiredFields();
});


    </script>
</body>
</html>
<?php
$conn->close();
?>

