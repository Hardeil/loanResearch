<?php
include_once "dbConnection/dbConnection.php";
session_start();

$userId = isset($_GET['userId']) ? intval($_GET['userId']) : 0;

if ($userId <= 0) {
    echo "Invalid user.";
    exit;
}

$sql = "SELECT id, first_name, last_name, total_balance, created_at FROM savings_tbl WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "User not found.";
    exit;
}
$sql1 = "SELECT client_cid, first_name, middle_name, last_name, center_name, birth_date, contact, gender, marital_status, education, country, region, province, zip_code, dosri, loan_amount, total_balance, remaining_balance, duration, request_date, due_date, pay_date, penalty_date FROM loan_list WHERE first_name = ? AND last_name = ? LIMIT 1";
$stmt1 = $conn->prepare($sql1);
$stmt1->bind_param("ss", $user["first_name"], $user["last_name"]);
$stmt1->execute();
$result = $stmt1->get_result();
$loanDetails = $result->fetch_assoc();
$stmt1->close();
$sql2 = "SELECT id FROM loan_list WHERE first_name = ? AND last_name = ? AND (status = 'Accept' OR status = 'Pending') LIMIT 1";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("ss", $user['first_name'], $user['last_name']);
$stmt2->execute();
$stmt2->store_result();

$hasActiveLoan = $stmt2->num_rows > 0;
$stmt2->close();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

    if ($action === 'deposit' && $amount > 0) {
        $sql = "UPDATE savings_tbl SET total_balance = total_balance + ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $amount, $userId);
        $currentDate = date('Y-m-d');
        $insertDepositSql = "INSERT INTO transaction (loan_id, savings_id, date, balance, status, type) VALUES ('$userId','$userId', '$currentData', '$amount','none', 'Savings')";
        if (!$conn->query($insertDepositSql)) {
            echo "Error inserting penalty: " . $conn->error;
        }
        if ($stmt->execute()) {
            echo "<script>
                    alert('Deposit Successfully');
                    window.location.href = 'specificSavingUser.php?userId=$userId';
                    </script>";
            exit;
        } else {
            echo "Error updating balance: " . $conn->error;
        }
        $stmt->close();
    } elseif ($action === 'withdraw' && $amount > 0) {
        $checkPendingSql = "SELECT id FROM transaction WHERE savings_id = ? AND type = 'Withdrawal' AND status = 'Pending'";
        $stmt = $conn->prepare($checkPendingSql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->close();
            echo "<script>
                    alert('You have a pending withdrawal. Please wait for it to be processed before making another withdrawal.');
                    window.location.href = 'specificSavingUser.php?userId=$userId';
                  </script>";
            exit;
        }
        $stmt->close();

        $sql = "SELECT total_balance FROM savings_tbl WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($totalBalance);
        $stmt->fetch();
        $stmt->close();

        // Validate withdrawal
        if ($totalBalance - $amount >= 200) {
            $insertWithdrawSql = "INSERT INTO transaction (loan_id, savings_id, date, balance, status, type) VALUES ('$userId', '$userId', '$currentData', '$amount', 'Pending', 'Withdrawal')";
            if (!$conn->query($insertWithdrawSql)) {
                echo "Error inserting withdrawal request: " . $conn->error;
            } else {
                echo "<script>
                        alert('Withdrawal request submitted successfully.');
                        window.location.href = 'specificSavingUser.php?userId=$userId';
                      </script>";
                exit;
            }
        } else {
            echo "<script>
                    alert('Insufficient balance. Your balance must remain at least 200 after withdrawal.');
                    window.location.href = 'specificSavingUser.php?userId=$userId';
                  </script>";
            exit;
        }
    }if ($action === 'loan_again') {
        $clientCID = $loanDetails['client_cid'];
        $firstName = $loanDetails['first_name'];
        $middleName = $loanDetails['middle_name'];
        $lastName = $loanDetails['last_name'];
        $centerName = $loanDetails['center_name'];
        $birthDate = $loanDetails['birth_date'];
        $contact = $loanDetails['contact'];
        $gender = $loanDetails['gender'];
        $maritalStatus = $loanDetails['marital_status'];
        $education = $loanDetails['education'];
        $country = $loanDetails['country'];
        $region = $loanDetails['region'];
        $province = $loanDetails['province'];
        $zipcode = $loanDetails['zip_code'];
        $dosri = $loanDetails['dosri'];
        $loanAmount = $conn->real_escape_string($_POST['loanAmount']);
        $duration = $conn->real_escape_string($_POST['duration']);
        $userId = $_SESSION['user_id'];
        $currentDate = date('Y-m-d');

        if ($loanAmount > 0 && $duration > 0) {
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
        } else {
            echo "<script>
                    alert('Invalid loan amount or duration.');
                    window.location.href = 'specificSavingUser.php?userId=$userId';
                  </script>";
            exit;
        }
    } else {
        echo "Invalid action or amount.";
    }
}
$sql1 = "SELECT id, savings_id, date, balance, type, status
				         FROM transaction
				         WHERE (type = 'Savings' OR type = 'Withdrawal') AND savings_id = $userId
				         ORDER BY id DESC";
$transaction = $conn->query($sql1);

$conn->close();
?>

				<!DOCTYPE html>
				<html lang="en">
				<head>
				    <meta charset="UTF-8">
				    <meta name="viewport" content="width=device-width, initial-scale=1.0">
				    <title>Loan Details</title>
				    <link rel="stylesheet" href="style.css">
				</head>
				<body>
				    <div class="container dashContainer">
				        <?php include_once "component/dashNav.php";?>
				        <div class="userDetailContainer">
				            <?php if ($user): ?>
				            <div class="backContainer">
				                <a href="savingsList" class="openFormBtn">Back</a>
				            </div>
				            <div class="userDetailCard">
				    <ul class="savingsHeader">
				        <h2>User Savings Details</h2>
				        <div>
				            <button id="openDepositFormBtn" class="openFormBtn">Add Deposit</button>
				            <button id="openWithdrawFormBtn" class="openFormBtn">Withdraw</button>
                            <?php if (!$hasActiveLoan): ?>
                                <button id="openLoanAgainFormBtn" class="openFormBtn">Reloan</button>
                            <?php endif;?>
				        </div>
				    </ul>

				    <div class="userInfo">
				        <div class="infoBox">
				            <h4>Name</h4>
				            <p><?php echo $user['first_name'] . " " . $user['last_name']; ?></p>
				        </div>
				        <div class="infoBox">
				            <h4>Total Balance</h4>
				            <p>P<?php echo number_format($user['total_balance'], 2); ?></p>
				        </div>
				        <div class="infoBox">
				            <h4>Account Created</h4>
				            <p><?php echo date("F j, Y", strtotime($user['created_at'])); ?></p>
				        </div>
				    </div>

				        <!-- Deposit Form -->
				        <div id="depositForm" class="popupForm">
				                        <div class="formContainer">
				                            <h2>Make a Deposit</h2>
				                            <form method="POST">
				                                <label for="amount">Deposit Amount:</label>
				                                <input type="number" id="amount" name="amount" step="0.01" required>
				                                <input type="hidden" name="action" value="deposit">
				                                <ul class="formBtn">
				                                    <button type="button" id="closeDepositFormBtn" class="cancelBtn">Cancel</button>
				                                    <button type="submit" class="submitBtn">Submit</button>
				                                </ul>
				                            </form>
				                        </div>
				                    </div>

				                    <!-- Withdraw Form -->
				                    <div id="withdrawForm" class="popupForm">
				                        <div class="formContainer">
				                            <h2>Make a Withdrawal</h2>
				                            <form method="POST">
				                                <label for="amount">Withdraw Amount:</label>
				                                <input type="number" id="amount" name="amount" step="0.01" required>
				                                <input type="hidden" name="action" value="withdraw">
				                                <ul class="formBtn">
				                                    <button type="button" id="closeWithdrawFormBtn" class="cancelBtn">Cancel</button>
				                                    <button type="submit" class="submitBtn">Submit</button>
				                                </ul>
				                            </form>
				                        </div>
				                    </div>
<!-- Loan Again Form -->
<div id="loanAgainForm" class="popupForm">
    <div class="formContainer">
        <h2>Do You Want to Loan Again?</h2>
        <form method="POST">
            <label for="loan_balance">Loan Amount:</label>
            <input type="number" id="loan_balance" name="loanAmount" step="0.01" required>

            <label for="loanDuration">Loan Duration:</label>
                    <select id="loanDuration" name="duration" required>
                        <option value="" disabled selected>Select duration</option>
                        <option value="28">1 Month</option>
                        <option value="84">3 Months</option>
                        <option value="336">1 Year</option>
                    </select>

            <input type="hidden" name="action" value="loan_again">

            <ul class="formBtn">
                <button type="button" id="closeLoanAgainFormBtn" class="cancelBtn">Cancel</button>
                <button type="submit" class="submitBtn">Submit</button>
            </ul>
        </form>
    </div>
</div>

				                    <!-- <div class="transactionTable"> -->
				    <h3>Transaction History</h3>
		            <div class="table savingsTbl">
				    <table >
				        <thead>
				            <tr>
				                <th>Date</th>
				                <th>Transaction Type</th>
				                <th>Amount</th>
				                <th>Status</th>
				            </tr>
				        </thead>
				        <tbody>
				            <?php if ($transaction->num_rows > 0): ?>
				                <?php while ($row = $transaction->fetch_assoc()): ?>
				                    <tr>
				                        <td><?php echo date("F j, Y", strtotime($row['date'])); ?></td>
				                        <td><?php echo htmlspecialchars($row['type']); ?></td>
				                        <td>P<?php echo number_format($row['balance'], 2); ?></td>
				                        <td><?php echo htmlspecialchars($row['status']); ?></td>
				                    </tr>
				                <?php endwhile;?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No transactions found.</td>
                </tr>
            <?php endif;?>
        </tbody>
    </table>
</div>

</div>

            <?php else: ?>
            <p>No user found.</p>
            <?php endif;?>
        </div>
    </div>

<script src="script.js"></script>
<script>
   document.addEventListener("DOMContentLoaded", function () {
    toggleForm("openDepositFormBtn", "closeDepositFormBtn", "depositForm");
    toggleForm("openWithdrawFormBtn", "closeWithdrawFormBtn", "withdrawForm");
    toggleForm("openLoanAgainFormBtn", "closeLoanAgainFormBtn", "loanAgainForm");
});

</script>
</body>
</html>
