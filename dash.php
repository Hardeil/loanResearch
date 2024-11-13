
<?php
include_once "dbConnection/dbConnection.php";
session_start();
$response = [];

$acceptedLoansSql = "SELECT COUNT(*) as count FROM loan_list WHERE status = 'Accepted'";
$acceptedLoansResult = $conn->query($acceptedLoansSql);
$response['acceptedLoans'] = $acceptedLoansResult->fetch_assoc()['count'];

$requestedLoansSql = "SELECT COUNT(*) as count FROM loan_list WHERE status = 'Requested'";
$requestedLoansResult = $conn->query($requestedLoansSql);
$response['requestedLoans'] = $requestedLoansResult->fetch_assoc()['count'];

$staffCountSql = "SELECT COUNT(*) as count FROM users WHERE role = 'Staff' AND status = 'Active'";
$staffCountResult = $conn->query($staffCountSql);
$response['totalStaff'] = $staffCountResult->fetch_assoc()['count'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container dashContainer">
        <?php include_once "component/dashNav.php";?>
        <div class="dashboard">
            <div class="stats-box">
                <h2>Accepted Loans</h2>
                <p id="acceptedLoans" class="number"><?php echo $response['acceptedLoans'] ?></p>
            </div>
            <div class="stats-box">
                <h2>Requested Loans</h2>
                <p id="requestedLoans" class="number"><?php echo $response['requestedLoans'] ?></p>
            </div>
            <div class="stats-box">
                <h2>Total Staff</h2>
                <p id="totalStaff" class="number"><?php echo $response['totalStaff'] ?></p>
            </div>
        </div>
    </div>
</body>
</html>

