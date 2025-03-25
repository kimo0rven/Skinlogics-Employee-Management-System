<?php
if (isset($_SESSION["user_account_id"]) && isset($_SESSION['username'])) {
    $user_account_id = $_SESSION["user_account_id"];

    $accountType = $_SESSION["account_type"];

    include 'js_php/database.php';
    include 'config.php';

    try {
        $sql = "SELECT * FROM employee WHERE user_account_id = :user_account_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_account_id', $user_account_id, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $employeeCount = "SELECT COUNT(*) AS total_records FROM employee";
        $employeeCount = $pdo->prepare($employeeCount);
        $employeeCount->execute();
        $employeeCount = $employeeCount->fetch(PDO::FETCH_ASSOC);
        $employeeCount = $employeeCount["total_records"];

        $activeCount = "SELECT COUNT(*) AS total_active FROM employee WHERE status = 'Active'";
        $activeCount = $pdo->prepare($activeCount);
        $activeCount->execute();
        $activeCount = $activeCount->fetch(PDO::FETCH_ASSOC);
        $activeCount = $activeCount["total_active"];

        $resignedCount = "SELECT COUNT(*) AS total_resigned FROM employee WHERE status = 'Resigned'";
        $resignedCount = $pdo->prepare($resignedCount);
        $resignedCount->execute();
        $resignedCount = $resignedCount->fetch(PDO::FETCH_ASSOC);
        $resignedCount = $resignedCount["total_resigned"];


        $terminatedCount = "SELECT COUNT(*) AS total_terminated FROM employee WHERE status = 'Terminated'";
        $terminatedCount = $pdo->prepare($terminatedCount);
        $terminatedCount->execute();
        $terminatedCount = $terminatedCount->fetch(PDO::FETCH_ASSOC);
        $terminatedCount = $terminatedCount["total_terminated"];
        if ($rows) {

            foreach ($rows as $row) {
                $first_name = $row['first_name'];
                $last_name = $row['last_name'];
            }
        } else {
            echo "No records found for user account ID: " . $user_account_id;
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    header("Location: index.php");
}
?>
<style>
    .profile-dropdown-content {
        display: none;
        position: absolute;
        background-color: #f9f9f9;
        min-width: 160px;
        box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
        z-index: 1;
        right: 0;
        margin-top: 200px;
        margin-right: 25px;
    }

    .profile-dropdown-content a {
        color: black;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
    }

    .profile-dropdown-content a:hover {
        background-color: #f1f1f1;
    }

    .profile-dropdown-container.show .profile-dropdown-content {
        display: block;
    }
</style>

<div id="admin">
    <div class="dashboard-background">
        <div class="dashboard-container">
            <div class="dashboard-navigation">
                <div class=".navigation-container ">
                    <div class="navigation-button">1</div>
                    <div class="navigation-button">2</div>
                    <div class="navigation-button">3</div>
                    <div class="navigation-button">4</div>
                    <div class="navigation-button">5</div>
                    <div class="navigation-button">6

                    </div>
                </div>

            </div>
            <div class="dashboard-content">
                <div class="dashboard-content-item1">
                    <div class="dashboard-content-header font-black">
                        <h1>DASHBOARD</h1>
                    </div>
                    <div id="logout-admin" class="dashboard-content-header font-medium">
                        <p><?php echo $first_name . " " . $last_name ?></p>
                        <img class="dashboard-content-header-img profile-dropdown-trigger"
                            src="assets/images/avatars/<?php echo $_SESSION['avatar'] ?>" alt="">
                        <div class="profile-dropdown-content">
                            <a href="#">Profile Settings</a>
                            <a href="#">Account Details</a>
                            <a href="logout.php">Logout</a>
                        </div>
                    </div>
                </div>
                <div class="dashboard-content-item2">
                    <div class="dashboard-main-content">
                        <div class="dashboard-kpi font-medium">
                            <div class="kpi-item">
                                <p class="kpi-label">Total Employees</p>
                                <div class="kpi-value-container">
                                    <p class="kpi-value"><?php echo $employeeCount; ?></p>
                                    <img src="./assets/images/employee.png" class="kpi-icon" alt="Employee Icon">
                                </div>
                            </div>
                            <div class="kpi-item">
                                <p class="kpi-label">Active Employees</p>
                                <div class="kpi-value-container">
                                    <p class="kpi-value"><?php echo $activeCount; ?></p>
                                    <img src="./assets/images/active.png" class="kpi-icon" alt="Employee Icon">
                                </div>
                            </div>
                            <div class="kpi-item">
                                <p class="kpi-label">Resigned Employees</p>
                                <div class="kpi-value-container">
                                    <p class="kpi-value"><?php echo $resignedCount; ?></p>
                                    <img src="./assets/images/resign.png" class="kpi-icon" alt="Employee Icon">
                                </div>
                            </div>
                            <div class="kpi-item">
                                <p class="kpi-label">Terminated Employees</p>
                                <div class="kpi-value-container">
                                    <p class="kpi-value"><?php echo $terminatedCount; ?></p>
                                    <img src="./assets/images/terminated.png" class="kpi-icon" alt="Employee Icon">
                                </div>
                            </div>
                        </div>
                        <div class="dashboard-chart">
                            1
                        </div>
                    </div>
                    <div class="dashboard-other-info"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById("logout-admin").addEventListener("click", function () {
        window.location.href = "logout.php";
    });


    document.addEventListener('DOMContentLoaded', function () {
        const dropdownContainer = document.querySelector('.profile-dropdown-container');
        const dropdownTrigger = document.querySelector('.profile-dropdown-trigger');

        dropdownTrigger.addEventListener('click', function (event) {
            event.stopPropagation();
            dropdownContainer.classList.toggle('show');
        });

        document.addEventListener('click', function (event) {
            if (!event.target.closest('.profile-dropdown-container')) {
                dropdownContainer.classList.remove('show');
            }
        });
    });
</script>

</html>