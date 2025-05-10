<?php
session_start();

if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
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

try {
    $sql = "SELECT * FROM employee";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("" . $e->getMessage());
}

?>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Admin'; ?> | Payroll</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">

    <!-- <style>
        /* Container needs to be positioned relative so the absolute dropdown positions correctly */
        .employee-detail-fields {
            position: relative;
            width: 300px;
            /* adjust as needed */
        }

        /* Dropdown container */
        .dropdown-content {
            border: 1px solid #ccc;
            display: none;
            position: absolute;
            background-color: #fff;
            z-index: 1000;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* Each result item */
        .result-item {
            padding: 8px;
            cursor: pointer;
        }

        .result-item:hover {
            background-color: #f1f1f1;
        }
    </style> -->
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
                                <form method="GET" action="payroll.php">
                                    <input type="text" id="employee_search" type="employee_search" name="search"
                                        placeholder="Search payroll..."
                                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                                    <button type="submit" style="margin: 0px">
                                        <img src="assets/images/icons/search-icon.png" alt="Search">
                                    </button>
                                    <div> <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                                            <a href="payroll.php" class="clear-search">Clear Search</a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>

                            <!-- <div class="employee-header-div"
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
                            </div> -->

                            <div class="employee-header-div">
                                <div class="flex gap-20">
                                    <div>
                                        <button class="employee-button">Run
                                            Payroll</button>
                                    </div>

                                    <div>
                                        <button class="employee-button" onClick="openAddEmployeeModal()">Create
                                            Payroll</button>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="employee-display">


                        </div>
                    </div>
                </div>
            </div>
        </div>



        <dialog style="border-radius: 10px; border: 1px solid #D1D1D1;" id="add_payroll_modal">

            <form action="hr_manager_leave_requests.php" method="POST" class="flex flex-column gap-20">
                <div>
                    <div id="modal_close_btn" style="position: absolute; top: 10px; right: 10px; cursor: pointer;"
                        onclick="closeAddEmployeeModal()">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 6L6 18" stroke="#333" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M6 6L18 18" stroke="#333" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-capitalize font-bold" style="font-size: 32px;margin: 0">Create Payroll</p>
                    </div>
                </div>
                <div id="modal-content" class="employee-detail-container">
                    <div class="employee-detail-container-group gap-20 font-medium">
                        <div clas="flex flex-row gap-20">
                            <div class="flex flex-row space-evenly gap-20">

                                <div class="employee-detail-fields" style="display: none">
                                    <label for="employee_id">Employee ID</label>
                                    <input id="employee_id" type="text" name="employee_id" readonly>
                                </div>

                                <div class="employee-detail-fields">
                                    <label for="employee-search">Employee<span style="color: red">*</span></label>
                                    <input type="text" id="employee-search" name="employee"
                                        placeholder="Type employee name..." required>
                                    <div id="employee-results" class="dropdown-content"></div>
                                </div>

                            </div>

                            <div class="flex flex-row gap-20 space-between">

                                <div class="employee-detail-fields flex flex-column">
                                    <label for="job_name">Job</label>
                                    <input id="job_name" type="text" name="job_name" readonly>
                                </div>

                                <div class="employee-detail-fields flex flex-column">
                                    <label for="department_name">Department</label>
                                    <input id="department_name" type="text" name="department_name" readonly>
                                </div>

                            </div>

                            <div class="flex flex-row gap-20 space-between">

                                <div class="employee-detail-fields flex flex-column">
                                    <label for="salary">Base Salary</label>
                                    <input id="salary" type="text" name="salary" readonly>
                                </div>

                                <div class="employee-detail-fields flex flex-column">
                                    <label for="bonus">bonus<span style="color: red">*</span></label>
                                    <input id="bonus" type="number" name="bonus" value="0" step=".01" required>
                                </div>

                            </div>


                            <div class="flex flex-row gap-20 space-between">
                                <div class="employee-detail-fields flex flex-column">
                                    <label for="payroll_start_date">Start Date</label>
                                    <input id="payroll_start_date" type="date" name="payroll_start_date">
                                </div>

                                <div class="employee-detail-fields flex flex-column">
                                    <label for="payroll_end_date">End Date</label>
                                    <input id="payroll_end_date" type="date" name="payroll_end_date">
                                </div>
                            </div>

                            <div style="padding: 10px 0px">
                                <div class="flex flex-row gap-20 space-between">
                                    <p style="margin:0px">Regular</p>
                                    <button style="padding: 5px">Fetch Data</button>
                                </div>
                                <div class="flex flex-row gap-20 space-between">
                                    <div class="employee-detail-fields flex flex-column">
                                        <label for="no_of_days">No of Days</label>
                                        <input id="no_of_days" type="number" name="no_of_days">
                                    </div>

                                    <div class="employee-detail-fields flex flex-column">
                                        <label for="total_hours_worked">Total Hours Worked</label>
                                        <input id="total_hours_worked" type="number" name="total_hours_worked">
                                    </div>
                                </div>
                            </div>

                            <div style="padding: 10px 0px">
                                <div class="flex flex-row gap-20 space-between">
                                    <p style="margin:0px">Overtime</p>
                                    <button style="padding: 5px">Fetch Data</button>
                                </div>
                                <div class="flex flex-row gap-20 space-between">
                                    <div class="employee-detail-fields flex flex-column">
                                        <label for="overtime_count">Overtime Count</label>
                                        <input id="overtime_count" type="number" name="overtime_count">
                                    </div>

                                    <div class="employee-detail-fields flex flex-column">
                                        <label for="overtime_total_hours">Overtime Total Hours</label>
                                        <input id="overtime_total_hours" type="number" name="overtime_total_hours">
                                    </div>
                                </div>
                            </div>

                            <div style="padding: 10px 0px">
                                <div class="flex flex-row gap-20 space-between">
                                    <p style="margin:0px">Leaves</p>
                                    <button style="padding: 5px">Fetch Data</button>
                                </div>
                                <div class="flex flex-row gap-20 space-between">
                                    <div class="employee-detail-fields flex flex-column">
                                        <label for="leave_count">Leave Count</label>
                                        <input id="leave_count" type="number" name="leave_count">
                                    </div>

                                    <div class="employee-detail-fields flex flex-column">
                                        <label for="leave_total_days">Leave Total Days</label>
                                        <input id="leave_total_days" type="number" name="leave_total_days">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>


                </div>
                <div class="employee-detail-edit-button-container">

                    <div>
                        <button class="employee-detail-edit-button" type="submit" value="approve"
                            name="approval">Approve</button>
                    </div>

                    <div>
                        <button style="background-color: var(--color-error)"
                            class="employee-detail-edit-button cancel-button" type="submit" value="reject"
                            name="approval">Reject</button>
                    </div>

                </div>
            </form>
        </dialog>
    </div>

</body>
<script>

    const addPayrollModal = document.getElementById('add_payroll_modal');

    function closeAddEmployeeModal() {
        addPayrollModal.close();
    }

    addPayrollModal.addEventListener('click', (e) => {
        if (e.target === addPayrollModal) {
            closeAddEmployeeModal();
        }
    });

    function openAddEmployeeModal() {
        addPayrollModal.showModal();
    }

    document.getElementById("employee-search").addEventListener("keyup", function () {
        var query = this.value;
        var resultsDiv = document.getElementById("employee-results");


        if (query.trim() === "") {
            resultsDiv.innerHTML = "";
            resultsDiv.style.display = "none";
            return;
        }
        console.log(query)
        fetch("includes/search_employee.php?q=" + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                console.log("Returned data:", data);

                resultsDiv.innerHTML = "";
                if (data.length > 0) {
                    console.log(data)
                    data.forEach(employee => {
                        var div = document.createElement("div");
                        div.className = "result-item";
                        div.textContent = employee.first_name + " " + employee.last_name;
                        div.dataset.employeeId = employee.employee_id;
                        div.addEventListener("click", function () {
                            document.getElementById("employee-search").value = employee.first_name + " " + employee.last_name;
                            document.getElementById("employee_id").value = employee.employee_id;
                            document.getElementById("job_name").value = employee.job_name;
                            document.getElementById("department_name").value = employee.department_name;
                            document.getElementById("salary").value = employee.salary;


                            resultsDiv.style.display = "none";
                        });
                        resultsDiv.appendChild(div);
                    });
                    resultsDiv.style.display = "block";
                } else {
                    resultsDiv.style.display = "none";
                }
            })
            .catch(error => {
                console.error("Error fetching employee data", error);
                resultsDiv.style.display = "none";
            });
    });

</script>

</html>