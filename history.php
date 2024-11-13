<?php
include_once "dbConnection/dbConnection.php";
session_start();

$searchTerm = "";
$sql = "SELECT id, first_name, last_name, address, contact, loan_amount, total_balance, remaining_balance, due_date, status
        FROM loan_list WHERE status='Completed'";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['searchTerm'])) {
    $searchTerm = $conn->real_escape_string($_POST['searchTerm']);
    $sql .= " AND (first_name LIKE '%$searchTerm%' OR last_name LIKE '%$searchTerm%' OR address LIKE '%$searchTerm%')";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container dashContainer">
        <?php include_once "component/dashNav.php";?>

        <div class="tableContainer">
            <ul class="tableTitle">
                <h2>Loan List</h2>
            </ul>
            <div class="searchBox">
            <form action="history.php" method="POST">
                <input type="text" name="searchTerm" class="searchInput" placeholder="Search by name or address..." value="<?php echo htmlspecialchars($searchTerm); ?>" />
            </form>
        </div>
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
                        </tr>
                    </thead>
                    <tbody>
                    <?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr onclick=\"window.location='specificLoanUser.php?userId={$row['id']}'\" style='cursor:pointer;'>";
        echo "<td>" . $row['first_name'] . "</td>";
        echo "<td>" . $row['last_name'] . "</td>";
        echo "<td>" . $row['address'] . "</td>";
        echo "<td>" . $row['contact'] . "</td>";
        echo "<td>P" . number_format($row['loan_amount'], 2) . "</td>";
        echo "<td>P" . number_format($row['total_balance'], 2) . "</td>";
        echo "<td>P" . number_format($row['remaining_balance'], 2) . "</td>";
        echo "<td>" . $row['due_date'] . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='8' class='noData'>No records found</td></tr>";
}
?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <script src="script.js"></script>

</body>
</html>
<?php
$conn->close();
?>
