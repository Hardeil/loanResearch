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
        <img src="img/b073c9e7-5ec3-43e2-b399-f2008f3b79e4-removebg-preview.png" alt="">
        <div class="menu">
            <a data-id="1" href="dash.php">Dashboard</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === "Staff"): ?>
                <a data-id="2" href="loanList.php">Add Loan</a>
                <a data-id="5" href="savingsList.php">Savings List</a>
                <a data-id="5" href="loanApproved.php">Approved Loans</a>
                <a data-id="7" href="history.php">History</a>
            <?php endif;?>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === "Admin"): ?>
                <a data-id="3" href="loanRequestList.php">Loan Request</a>
                <a data-id="4" href="withdrawalRequestList.php" class="small">Withdrawal Request</a>
                <a data-id="4" href="loanAcceptList.php">Approved Loans</a>
                <a data-id="6" href="userList.php">User List</a>
            <?php endif;?>
            <a href="changePassword.php" class="profile">Change Password</a>
        </div>
        <a href="#" id="logoutButton" class="logout">Logout</a>

        <div id="logoutConfirmationModal" class="popupForm">
            <div class="logoutForm">
                <h2>Do you really want to log out?</h2>
                <form id="logoutForm" method="POST" action="component/logout.php">
                    <ul class="formBtn">
                        <button type="button" id="cancelLogoutBtn" class="cancelBtn">Cancel</button>
                        <button type="submit" class="submitBtn">Yes, Logout</button>
                    </ul>
                </form>
            </div>
        </div>

    </div>

    <script src="script.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const logoutButton = document.getElementById("logoutButton");
            const logoutConfirmationModal = document.getElementById("logoutConfirmationModal");
            const cancelLogoutBtn = document.getElementById("cancelLogoutBtn");

            logoutButton.addEventListener("click", function (e) {
                e.preventDefault();
                logoutConfirmationModal.style.display = "block";
            });

            cancelLogoutBtn.addEventListener("click", function () {
                logoutConfirmationModal.style.display = "none";
            });
        });
    </script>
</body>
