<?php
include_once "dbConnection/dbConnection.php";
session_start();

$searchTerm = "";
$sql = "SELECT id, first_name, last_name, total_balance, date, created_at FROM savings_tbl";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['searchTerm'])) {
    $searchTerm = $conn->real_escape_string($_POST['searchTerm']);
    $sql .= " AND (first_name LIKE '%$searchTerm%' OR last_name LIKE '%$searchTerm%')";
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
                <h2>Savigs List</h2>
            </ul>
            <div class="searchBox">
            <form action="loanApproved.php" method="POST">
                <input type="text" name="searchTerm" class="searchInput" placeholder="Search by name " value="<?php echo htmlspecialchars($searchTerm); ?>" />
            </form>
        </div>
            <div class="table">
                <table>
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Total Savings</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr onclick=\"window.location='specificSavingUser.php?userId={$row['id']}'\" style='cursor:pointer;'>";
        echo "<td>" . $row['first_name'] . "</td>";
        echo "<td>" . $row['last_name'] . "</td>";
        echo "<td>P" . number_format($row['total_balance'], 2) . "</td>";
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
