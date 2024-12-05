<?php
include_once "dbConnection/dbConnection.php";
session_start();
$userId = isset($_GET['userId']) ? intval($_GET['userId']) : 0;

$user = null;
$totalDeposit = 0;
if ($userId > 0) {
    $sql = "SELECT id, first_name, last_name, contact, loan_amount, total_balance, remaining_balance, request_date, due_date, pay_date, penalty_date, status FROM loan_list WHERE id = $userId";
    $result = $conn->query($sql);

    // Fetch penalty dates
    $penaltyDates = [];
    $sql1 = "SELECT date FROM transaction WHERE loan_id = $userId AND type='penalty'";
    $result1 = $conn->query($sql1);
    if ($result1 && $result1->num_rows > 0) {
        while ($penaltyRow = $result1->fetch_assoc()) {
            $penaltyDates[] = $penaltyRow['date'];
        }
    }

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        $requestDate = new DateTime($user['request_date']);
        $dueDate = new DateTime($user['due_date']);
        $interval = $requestDate->diff($dueDate);

        $totalWeeks = floor($interval->days / 7) + 1;

        if ($totalWeeks > 0) {
            $weeklyPayment = $user['total_balance'] / $totalWeeks;

            $paymentSchedule = [];
            $currentDate = clone $requestDate;
            $todaysDate = date('Y-m-d');

            for ($i = 0; $i < $totalWeeks; $i++) {
                $status = "-";
                $loopDateStr = $currentDate->format('Y-m-d');
                $status = ($user['pay_date'] > $loopDateStr) ? "Paid" : "-";

                if (!empty($penaltyDates)) {
                    $penalty = in_array($loopDateStr, $penaltyDates) ? "Yes" : ($loopDateStr < min($penaltyDates) ? "-" : "No");
                } else {
                    $penalty = "-";
                }

                $paymentAmount = in_array($loopDateStr, $penaltyDates) ? $weeklyPayment + 100 : $weeklyPayment;

                if ($loopDateStr <= $todaysDate) {
                    $totalDeposit += $paymentAmount;
                }

                $paymentSchedule[] = [
                    'payment_due' => $loopDateStr,
                    'payment_amount' => number_format($paymentAmount, 2),
                    'status' => $status,
                    'penalty' => $penalty,
                ];

                $currentDate->modify('+7 days');
            }

        } else {
            $weeklyPayment = $user['total_balance'];
            $paymentSchedule = [
                [
                    'payment_due' => $requestDate->format('Y-m-d'),
                    'payment_amount' => number_format($weeklyPayment, 2),
                ],
            ];
        }
        $currentDate = date('Y-m-d');
        if ($currentDate > $user['penalty_date'] && $user['status'] === "Accept") {
            $penaltyAmount = 100;
            $newRemainingBalance = $user['remaining_balance'] + $penaltyAmount;
            $penaltyDate = date('Y-m-d', strtotime($user['penalty_date'] . ' +7 days'));
            $updateSql = "UPDATE loan_list SET remaining_balance = '$newRemainingBalance', penalty_date = '$penaltyDate' WHERE id = $userId";
            $conn->query($updateSql);
            $penaltyDate = date('Y-m-d', strtotime($currentDate . ' -1 day'));
            $insertPenaltySql = "INSERT INTO transaction (loan_id,savings_id, date, balance, status, type) VALUES ('$userId', '$userId', '$penaltyDate', '$penaltyAmount', 'none','Penalty')";
            if (!$conn->query($insertPenaltySql)) {
                echo "Error inserting penalty: " . $conn->error;
            }
            $user['remaining_balance'] = $newRemainingBalance;
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $depositAmount = $conn->real_escape_string($_POST['depositAmount']);
            if ($totalDeposit > $depositAmount) {
                echo "<script>
                alert('Loan Deposit is not enough.');
                window.location.href = 'specificLoanUser.php?userId=$userId';
                </script>";
                exit;
            }
            $newRemainingBalance = $user['remaining_balance'] - $depositAmount;
            $currentDate = $user['pay_date'];
            $payDate = date('Y-m-d', strtotime($currentDate . ' + 7 days'));
            $penaltyDate = $user['pay_date'] >= $user['penalty_date'] ? $payDate : $user['penalty_date'];
            $updateSql = "UPDATE loan_list SET remaining_balance = '$newRemainingBalance', pay_date = '$payDate', penalty_date = '$penaltyDate' WHERE id = $userId";

            if ($conn->query($updateSql) === true) {
                $user['remaining_balance'] = $newRemainingBalance;
                if ($totalDeposit > $depositAmount) {
                    echo "<script>
                    alert('Loan Deposit is not enough.');
                    window.location.href = 'specificLoanUser.php?userId=$userId';
                    </script>";
                    exit;
                } else {
                    $insertDepositSql = "INSERT INTO transaction (loan_id,savings_id, date, balance,status, type) VALUES ($userId,$userId, '$currentDate', '$depositAmount','none', 'Deposit')";
                    $conn->query($insertDepositSql);

                    if ($newRemainingBalance <= 0) {
                        $conn->query("UPDATE loan_list SET status = 'Completed' WHERE id = $userId");
                        $user['status'] = 'Completed';

                        $sqlSelect = "SELECT id, total_balance FROM savings_tbl WHERE first_name = ? AND last_name = ?";
                        $stmtSelect = $conn->prepare($sqlSelect);

                        if ($stmtSelect) {
                            $stmtSelect->bind_param("ss", $user['first_name'], $user['last_name']);
                            $stmtSelect->execute();
                            $resultSelect = $stmtSelect->get_result();

                            if ($resultSelect && $resultSelect->num_rows > 0) {
                                $savingsData = $resultSelect->fetch_assoc();
                                $savingsId = $savingsData['id'];
                                $currentBalance = $savingsData['total_balance'];
                                $amount = $user['loan_amount'] * 0.02;
                                $sqlUpdate = "UPDATE savings_tbl SET total_balance = total_balance + ? WHERE id = ?";
                                $stmtUpdate = $conn->prepare($sqlUpdate);

                                if ($stmtUpdate) {
                                    $stmtUpdate->bind_param("di", $amount, $savingsId);
                                    if ($stmtUpdate->execute()) {
                                        echo "<script>
                    alert('Savings updated successfully.');
                    window.location.href = 'specificLoanUser.php?userId=$userId';
                </script>";
                                    } else {
                                        echo "Error updating savings: " . $stmtUpdate->error;
                                    }
                                    $stmtUpdate->close();
                                } else {
                                    echo "Error preparing update statement: " . $conn->error;
                                }

                            } else {
                                echo "<script>
            alert('Savings account not found.');
            window.location.href = 'specificLoanUser.php?userId=$userId';
        </script>";
                            }
                            $stmtSelect->close();
                        } else {
                            echo "Error preparing select statement: " . $conn->error;
                        }

                    }

                    echo "<script>
                    alert('Loan Deposit successfully.');
                    window.location.href = 'specificLoanUser.php?userId=$userId';
                    </script>";
                    exit;
                }

            } else {
                echo "Error: " . $conn->error;
            }
        }

    } else {
        echo "<script>
        alert('No user found.');
        window.location.href = 'loanList.php';
        </script>";
        exit;
    }

    $totalPenaltyQuery = "SELECT SUM(balance) AS total_penalty FROM transaction WHERE loan_id = $userId AND type = 'penalty'";
    $totalPenaltyResult = $conn->query($totalPenaltyQuery);
    $totalPenalty = $totalPenaltyResult && $totalPenaltyResult->num_rows > 0 ? $totalPenaltyResult->fetch_assoc()['total_penalty'] : 0;
} else {
    echo "Invalid user.";
    exit;
}

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
            <?php if ($user['status'] === 'Completed'): ?>
                <a href="history.php" class="openFormBtn">Back</a>
            <?php else: ?>
                <a href="loanApproved.php" class="openFormBtn">Back</a>
            <?php endif;?>

            </div>
            <ul class="userDetailTitle">
                <h2>User Loan Details</h2>
                <?php if ($user['status'] !== 'Completed'): ?>
                    <button id="openFormBtn" class="openFormBtn">Add Deposit</button>
                <?php endif;?>
            </ul>
            <ul class="userDetailBody">
                <div class="userDetail">
                    <p><strong>First Name:</strong> <?php echo $user['first_name']; ?></p>
                    <p><strong>Last Name:</strong> <?php echo $user['last_name']; ?></p>
                    <p><strong>Contact:</strong> <?php echo $user['contact']; ?></p>
                    <p><strong>Loan Amount:</strong> P<?php echo number_format($user['loan_amount'], 2); ?></p>
                    <p><strong>Total Penalty:</strong> P<?php echo number_format($totalPenalty, 2); ?></p>
                    <p><strong>Remaining Balance:</strong> P<?php echo number_format($user['remaining_balance'], 2); ?></p>
                    <p><strong>Started Date:</strong> <?php echo $user['request_date']; ?></p>
                    <p><strong>Due Date:</strong> <?php echo $user['due_date']; ?></p>
                    <p><strong>Status:</strong> <?php echo $user['status']; ?></p>
                </div>
                <li>
                    <h2>Payment Schedule (Every week)</h2>
                    <div  class="table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Payment Due Date</th>
                                    <th>Amount Due</th>
                                    <th>Status</th>
                                    <th>Penalty</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($paymentSchedule, 1) as $payment): ?>
									<tr>
										<td><?php echo $payment['payment_due']; ?></td>
										<td>P<?php echo $payment['payment_amount']; ?></td>
										<td><?php echo $payment['status']; ?></td>
										<td><?php echo $payment['penalty']; ?></td>
									</tr>
								<?php endforeach;?>
                            </tbody>
                        </table>
                    </div>
                </li>
            </ul>

            <div id="form" class="popupForm">
                <div class="formContainer">
                    <h2>Make a Deposit</h2>
                    <form action="" method="POST">
                        <label for="depositAmount">Deposit Amount:</label>
                        <input type="number" id="depositAmount" name="depositAmount" step="0.01" required>

                        <ul class="formBtn">
                            <button type="submit" class="submitBtn">Submit</button>
                            <button type="button" id="closeFormBtn" class="cancelBtn">Cancel</button>
                        </ul>
                    </form>
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
        toggleForm("openFormBtn", "closeFormBtn", "form");
  });
</script>
</body>
</html>
