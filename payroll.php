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

try {
    $sql = 'SELECT * FROM payroll';
    $stmt_payroll = $pdo->prepare($sql);
    $stmt_payroll->execute();
    $payrolls = $stmt_payroll->fetch(PDO::FETCH_ASSOC);

    print_r($payrolls);
} catch (PDOException $e) {
    echo $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $payrollStart = isset($_POST['payroll_start']) ? $_POST['payroll_start'] : null;
    $payrollEnd = isset($_POST['payroll_end']) ? $_POST['payroll_end'] : null;

} else {
    echo "Invalid request method. Please submit the form.";
}


?>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Admin'; ?> | Payroll</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
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
                                    <form id="payrollForm" action="#" method="POST">
                                        <div>
                                            <input type="date" id="payroll_start" name="payroll_start"
                                                value="<?php echo $payrollStart ?>">
                                        </div>
                                        <div>
                                            <p style="margin: 0; text-align: center;"> - </p>
                                        </div>
                                        <div>
                                            <input type="date" id="payroll_end" name="payroll_end"
                                                value="<?php echo $payrollEnd ?>">
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

    <script>
        const payrollStart = document.getElementById('payroll_start');
        const payrollEnd = document.getElementById('payroll_end');
        const form = document.getElementById('payrollForm');

        function validateDates() {
            const startDate = payrollStart.value;
            const endDate = payrollEnd.value;

            if (!startDate && endDate) {
                alert('Please select a start date before selecting an end date.');
                return false;
            }

            if (startDate && endDate && new Date(startDate) >= new Date(endDate)) {
                alert('Start date must be earlier than the end date.');
                return false;
            }

            return true;
        }

        form.addEventListener('submit', (event) => {
            event.preventDefault();

            if (validateDates()) {
                const formData = new FormData(form);

                fetch('your_php_script.php', {
                    method: 'POST',
                    body: formData,
                })
                    .then((response) => response.text())
                    .then((data) => {
                        console.log('Server response:', data);
                        alert('Form successfully submitted!');
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                        alert('There was an error submitting the form.');
                    });
            }
        });

        payrollStart.addEventListener('change', () => {
            if (validateDates() && payrollEnd.value) {
                form.submit();
            }
        });

        payrollEnd.addEventListener('change', () => {
            if (validateDates()) {
                console.log('End date selected:', payrollEnd.value);
                form.submit();
            }
        });

    </script>

</body>

</html>