<?php
session_start();

if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

$user_account_id = (int) $_SESSION["user_account_id"];
$accountType = $_SESSION["account_type"] ?? '';

include 'includes/database.php';
include 'config.php';


?>


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Admin'; ?> | Payroll</title>
    <link rel="stylesheet" href="style.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.min.js"></script>
</head>

<body class="font-medium">
    <div id="admin">
        <div class="dashboard-background">
            <div class="dashboard-container">
                <div class="dashboard-navigation">
                    <?php include('includes/navigation.php') ?>
                </div>
                <div class="dashboard-content">
                    <div class="dashboard-content-item1">
                        <div class="dashboard-content-header font-black">
                            <h1>PAYROLL</h1>
                        </div>
                        <div id="logout-admin" class="dashboard-content-header font-medium">
                            <?php include('includes/header-avatar.php') ?>
                        </div>
                    </div>

                    <div class="employee-main-content">
                        <div class="employee-header">
                            <div class="employee-header-div">
                                <form method="GET" action="employees.php">

                                    <input type="text" id="employee_search" type="employee_search" name="search"
                                        placeholder="Search employees..."
                                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                                    <button type="submit" style="margin: 0px">
                                        <img src="assets/images/icons/search-icon.png" alt="Search">
                                    </button>
                                    <div> <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                                            <a href="employees.php" class="clear-search">Clear Search</a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>

                            <div class="employee-header-div"
                                style="display: flex; flex-direction: row; justify-content: center;">
                                <div class="payroll-range-container">
                                    <form id="payrollForm">
                                        <div>
                                            <input type="date" id="payroll_start" name="payroll_start" value="">
                                        </div>
                                        <div>
                                            <p style="margin: 0; text-align: center;"> - </p>
                                        </div>
                                        <div>
                                            <input type="date" id="payroll_end" name="payroll_end" value="">
                                        </div>
                                        <div>
                                            <button type="button" id="queryPayrollButton">Query Payroll</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="employee-header-div">
                                <div><button class="employee-button" onClick="openAddEmployeeModal()">Create
                                        Payroll</button></div>
                            </div>

                        </div>

                        <div class="employee-display">


                        </div>
                    </div>
                </div>
            </div>
        </div>



        <dialog style="border-radius: 10px; border: 1px solid #D1D1D1; width: 70%;" id="add_employee_modal">

            1
        </dialog>
    </div>


</body>

</html>