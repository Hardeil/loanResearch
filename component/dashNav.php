<?php

if (!isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

?>
<body>
    <div class="dashNav">
        <!-- <h3 style="color:blue">Loan<span style="color:rgb(205, 205, 3)">App</span></h3> -->
         <img src="img/b073c9e7-5ec3-43e2-b399-f2008f3b79e4-removebg-preview.png" alt="">
        <div class="menu">
            <a data-id="1" href="dash.php" >Dashboard</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === "Staff"): ?>
                <a data-id="2" href="loanList.php">Add Loan</a>
                <a data-id="5" href="loanApproved.php">Approved Loans </a>
                <a data-id="7" href="history.php">History</a>
            <?php endif;?>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === "Admin"): ?>
                <a data-id="3" href="loanRequestList.php">Loan Request</a>
                <a data-id="4" href="loanAcceptList.php">Approved Loans</a>
                <a data-id="6" href="userList.php">User List</a>
            <?php endif;?>
            <a href="changePassword.php" class="profile">Change Password</a>
        </div>
        <a href="component/logout.php" class="logout">Logout</a>
    </div>
    <script src="script.js"></script>
</body>
