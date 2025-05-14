<?php
session_start();

include_once 'includes/database.php';
include_once 'config.php';

if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

$user_account_id = (int) $_SESSION["user_account_id"];
$accountType = $_SESSION["account_type"] ?? '';

function get_date_ranges()
{
    $today = date('j');
    $currentYear = date('Y');
    $currentMonth = date('m');

    if ($today < 15) {
        $startDate = date('Y-m-d', strtotime("$currentYear-$currentMonth-01"));
        $endDate = date('Y-m-d', strtotime("$currentYear-$currentMonth-15"));
    } else {
        $startDate = date('Y-m-d', strtotime("$currentYear-$currentMonth-16"));
        $endDate = date('Y-m-t', strtotime("$currentYear-$currentMonth-01"));
    }
    return [
        'start_date' => $startDate,
        'end_date' => $endDate
    ];
}

$startDate = '';
$endDate = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_payroll'])) {

        $employeeId = filter_input(INPUT_POST, 'create_employee_id', FILTER_VALIDATE_INT);
        $baseSalary = filter_input(INPUT_POST, 'create_salary', FILTER_VALIDATE_FLOAT);
        $bonus = filter_input(INPUT_POST, 'create_bonus', FILTER_VALIDATE_FLOAT);
        $netPay = filter_input(INPUT_POST, 'create_net_pay', FILTER_VALIDATE_FLOAT);
        $payrollStartDate = filter_input(INPUT_POST, 'create_payroll_start_date', FILTER_SANITIZE_SPECIAL_CHARS);
        $payrollEndDate = filter_input(INPUT_POST, 'create_payroll_end_date', FILTER_SANITIZE_SPECIAL_CHARS);
        $sssDeduction = filter_input(INPUT_POST, 'create_sss_deduction', FILTER_VALIDATE_FLOAT);
        $philhealthDeduction = filter_input(INPUT_POST, 'create_philhealth_deduction', FILTER_VALIDATE_FLOAT);
        $pagibigDeduction = filter_input(INPUT_POST, 'create_pagibig_deduction', FILTER_VALIDATE_FLOAT);
        $taxDeduction = filter_input(INPUT_POST, 'create_tax_deduction', FILTER_VALIDATE_FLOAT);
        $otherDeduction = filter_input(INPUT_POST, 'create_other_deduction', FILTER_VALIDATE_FLOAT);
        $noOfDays = filter_input(INPUT_POST, 'create_no_of_days', FILTER_VALIDATE_INT);
        $totalHoursWorked = filter_input(INPUT_POST, 'create_total_hours_worked', FILTER_VALIDATE_FLOAT);
        $overtimeCount = filter_input(INPUT_POST, 'create_overtime_count', FILTER_VALIDATE_INT);
        $overtimeTotalHours = filter_input(INPUT_POST, 'create_overtime_total_hours', FILTER_VALIDATE_FLOAT);
        $leaveCount = filter_input(INPUT_POST, 'create_leave_count', FILTER_VALIDATE_INT);
        $leaveTotalDays = filter_input(INPUT_POST, 'create_leave_total_days', FILTER_VALIDATE_FLOAT);
        $workPay = filter_input(INPUT_POST, 'create_work_pay', FILTER_VALIDATE_FLOAT);
        $overtimePay = filter_input(INPUT_POST, 'create_overtime_pay', FILTER_VALIDATE_FLOAT);
        $leavePay = filter_input(INPUT_POST, 'create_leave_pay', FILTER_VALIDATE_FLOAT);

        if (
            $employeeId === false ||
            $baseSalary === false ||
            $bonus === false ||
            $netPay === false ||
            is_null($payrollStartDate) ||
            is_null($payrollEndDate) ||
            $sssDeduction === false ||
            $philhealthDeduction === false ||
            $pagibigDeduction === false ||
            $taxDeduction === false ||
            $otherDeduction === false ||
            $noOfDays === false ||
            $totalHoursWorked === false ||
            $overtimeCount === false ||
            $overtimeTotalHours === false ||
            $leaveCount === false ||
            $leaveTotalDays === false ||
            $workPay === false ||
            $overtimePay === false ||
            $leavePay === false
        ) {
            echo "Error: Invalid or missing data. Please check all fields.";
            exit;
        }

        try {
            new DateTime($payrollStartDate);
            new DateTime($payrollEndDate);
        } catch (Exception $e) {
            echo "Error: Invalid date format. Please use YYYY-MM-DD.";
            exit;
        }

        $insertSql = 'INSERT INTO payroll (
                employee_id, base_salary, bonus, net_pay,
                payroll_start_date, payroll_end_date,
                sss_deduction, philhealth_deduction, pagibig_deduction, tax_deduction, other_deduction,
                no_of_days, total_hours_worked, overtime_count, overtime_total_hours,
                leave_count, leave_total_days, work_pay, overtime_pay, leave_pay
            ) VALUES (
                :employee_id, :base_salary, :bonus, :net_pay,
                :payroll_start_date, :payroll_end_date,
                :sss_deduction, :philhealth_deduction, :pagibig_deduction, :tax_deduction, :other_deduction,
                :no_of_days, :total_hours_worked, :overtime_count, :overtime_total_hours,
                :leave_count, :leave_total_days, :work_pay, :overtime_pay, :leave_pay
            )';

        try {
            $stmtInsert = $pdo->prepare($insertSql);
            $stmtInsert->execute([
                ':employee_id' => $employeeId,
                ':base_salary' => $baseSalary,
                ':bonus' => $bonus,
                ':net_pay' => $netPay,
                ':payroll_start_date' => $payrollStartDate,
                ':payroll_end_date' => $payrollEndDate,
                ':sss_deduction' => $sssDeduction,
                ':philhealth_deduction' => $philhealthDeduction,
                ':pagibig_deduction' => $pagibigDeduction,
                ':tax_deduction' => $taxDeduction,
                ':other_deduction' => $otherDeduction,
                ':no_of_days' => $noOfDays,
                ':total_hours_worked' => $totalHoursWorked,
                ':overtime_count' => $overtimeCount,
                ':overtime_total_hours' => $overtimeTotalHours,
                ':leave_count' => $leaveCount,
                ':leave_total_days' => $leaveTotalDays,
                ':work_pay' => $workPay,
                ':overtime_pay' => $overtimePay,
                ':leave_pay' => $leavePay,
            ]);
            echo "Payroll record created successfully!";
        } catch (PDOException $e) {
            echo "Error creating payroll record: " . $e->getMessage();
        }

        $defaults = get_date_ranges();
        $startDate = $defaults['start_date'];
        $endDate = $defaults['end_date'];

    } else {
        $startDate = $_POST['payroll_start'] ?? '';
        $endDate = $_POST['payroll_end_date'] ?? '';
        if (empty($startDate) || empty($endDate)) {
            echo "Error: Both start and end dates are required.";
            exit();
        }
        if (strtotime($endDate) < strtotime($startDate)) {
            echo "Error: The end date must be later than or equal to the start date.";
            exit();
        }
    }
} else {
    $defaults = get_date_ranges();
    $startDate = $defaults['start_date'];
    $endDate = $defaults['end_date'];
}

function formatDate($rawDate, $format = 'M d, Y')
{
    if (!empty($rawDate)) {
        try {
            $date = new DateTime($rawDate);
            return htmlspecialchars($date->format($format));
        } catch (Exception $e) {
            return 'Invalid Date';
        }
    }
    return 'N/A';
}

$search = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = '%' . trim($_GET['search']) . '%';
}

$sqlQuery = "SELECT
                e.first_name,
                e.last_name,
                e.job_id,
                e.user_account_id,
                ua.avatar,
                j.*,
                d.*,
                p.*
            FROM payroll p
            INNER JOIN employee e ON p.employee_id = e.employee_id
            INNER JOIN job j ON e.job_id = j.job_id
            INNER JOIN department d ON j.department_id = d.department_id
            INNER JOIN user_account ua ON e.user_account_id = ua.user_account_id ";

if ($search) {
    $sqlQuery .= "WHERE (e.first_name LIKE :search OR 
                       e.last_name LIKE :search OR 
                       j.job_name LIKE :search OR 
                       d.department_name LIKE :search) ";
} else {

    $sqlQuery .= "WHERE p.payroll_start_date >= :start_date
                   AND p.payroll_end_date <= :end_date ";
}
$sqlQuery .= "ORDER BY p.payroll_start_date DESC";

$stmtRetrieval = $pdo->prepare($sqlQuery);

if ($search) {
    $stmtRetrieval->bindValue(':search', $search);
} else {
    $stmtRetrieval->bindParam(':start_date', $startDate);
    $stmtRetrieval->bindParam(':end_date', $endDate);
}

$stmtRetrieval->execute();
$payrolls = $stmtRetrieval->fetchAll(PDO::FETCH_ASSOC);

?>



<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Admin'; ?> | Payroll</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">

    <style>
        .employee-detail-fields {
            position: relative;
            width: 300px;

        }

        .dropdown-content {
            border: 1px solid #ccc;
            display: none;
            background-color: #fff;
            z-index: 1000;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .result-item {
            padding: 8px;
            cursor: pointer;
        }

        .result-item:hover {
            background-color: #f1f1f1;
        }
    </style>
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
                                    <input type="text" id="employee_search" name="search"
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

                            <div class="employee-header-div"
                                style="display: flex; flex-direction: row; justify-content: center;">
                                <div class="payroll-range-container">
                                    <form id="payrollForm" action="#" method="POST">
                                        <div>
                                            <!-- <label for="payroll_start">Start Date:</label> -->
                                            <input type="date" id="payroll_start" name="payroll_start"
                                                value="<?php echo $startDate ?>">
                                        </div>
                                        <div>
                                            <p style="margin: 0; text-align: center;"> - </p>
                                        </div>
                                        <div>
                                            <!-- <label for="payroll_end_date">End Date:</label> -->
                                            <input type="date" id="payroll_end_date" name="payroll_end_date"
                                                value="<?php echo $endDate ?>">
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="employee-header-div">
                                <div class="flex gap-20">
                                    <!-- <div>
                                        <button class="employee-button">Run
                                            Payroll</button>
                                    </div> -->

                                    <div>
                                        <button class="employee-button" onClick="openAddEmployeeModal()">Create
                                            Payroll</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="employee-display" style="overflow: auto;">
                            <table class="employee-table">
                                <thead class="font-bold">
                                    <tr>
                                        <th>Avatar</th>
                                        <th>Employee</th>
                                        <th>Department</th>
                                        <th>Job</th>
                                        <th>Date</th>
                                        <th>SSS Dudection</th>
                                        <th>Philhealth Deduction</th>
                                        <th>Pagibig Deduction</th>
                                        <th>Tax Deduction</th>
                                        <th>Net Pay</th>
                                    </tr>
                                </thead>
                                <tbody class="font-medium">
                                    <?php if (!empty($payrolls)): ?>
                                        <?php foreach ($payrolls as $payroll): ?>
                                            <tr id="row-<?php echo $payroll['employee_id'] ?>" style="white-space: nowrap;"
                                                data-user='<?php echo json_encode($payroll); ?>'
                                                class="employee-display-list text-center"
                                                onclick="openPayrollModal(<?php echo $payroll['employee_id'] ?>)">
                                                <td style="text-align: center;">
                                                    <img class="margin-right: 10px"
                                                        src="assets/images/avatars/<?php echo !empty($payroll['avatar']) ? htmlspecialchars($payroll['avatar']) : 'default.png'; ?>"
                                                        alt="">
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($payroll['first_name']) . " " . htmlspecialchars($payroll['last_name']); ?>
                                                </td>
                                                <td style="text-align: center;">
                                                    <?php echo htmlspecialchars($payroll['job_name']); ?>
                                                </td>
                                                <td style="text-align: center;">
                                                    <?php echo htmlspecialchars($payroll['department_name']); ?>
                                                </td>
                                                <td style="text-align: center;">
                                                    <?php echo formatDate($payroll['payroll_start_date']) . " - " . formatDate($payroll['payroll_end_date']) ?>
                                                </td>
                                                <td style="text-align: center;">
                                                    <?php echo htmlspecialchars($payroll['sss_deduction']); ?>
                                                </td>
                                                <td style="text-align: center;">
                                                    <?php echo htmlspecialchars($payroll['philhealth_deduction']); ?>
                                                </td>
                                                <td style="text-align: center;">
                                                    <?php echo htmlspecialchars($payroll['pagibig_deduction']); ?>
                                                </td>
                                                <td style="text-align: center;">
                                                    <?php echo htmlspecialchars($payroll['tax_deduction']); ?>
                                                </td>



                                                <td style="text-align: center;">
                                                    <?php echo htmlspecialchars($payroll['net_pay']); ?>
                                                </td>

                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9">No payroll found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <dialog id="payroll_details">
        <form action="payroll.php" method="POST" class="flex flex-column gap-20">
            <div>
                <div id="modal_close_btn" style="position: absolute; top: 10px; right: 10px; cursor: pointer;"
                    onclick="closePayrollModal()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18" stroke="#333" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                        <path d="M6 6L18 18" stroke="#333" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </div>

            </div>
            <div id="modal-content" class="employee-detail-container flex flex-column justify-center">

                <div class="flex flex-column justify-center gap-20 font-medium">
                    <div class="flex flex-row space-evenly gap-20">

                        <div class="employee-detail-fields" style="display: none">
                            <label for="employee_id">Employee ID</label>
                            <input id="employee_id" type="text" name="employee_id" readonly>
                        </div>

                        <div class="employee-detail-fields">
                            <label for="employee-search">Employee</label>
                            <input type="text" id="employee_name" name="employee_name" readonly>
                        </div>
                    </div>
                </div>


                <div class="flex flex-column space-between gap-20 font-medium">

                    <div class="flex flex-row space-between gap-20">

                        <div class="flex flex-column">
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
                                    <input id="payroll_start_date" type="date" name="payroll_start_date"
                                        value="2025-03-01">
                                </div>

                                <div class="employee-detail-fields flex flex-column">
                                    <label for="payroll_end_date">End Date</label>
                                    <input id="payroll_end_date" type="date" name="payroll_end_date" value="2025-05-15">
                                </div>
                            </div>

                            <div style="padding: 10px 0px;">
                                <div class="flex flex-row gap-20 space-between">
                                    <p style="margin:0px" class="font-bold">Regular</p>

                                </div>
                                <div class="flex flex-row gap-20 space-between">
                                    <div class="employee-detail-fields flex flex-column">
                                        <label for="no_of_days">No of Days</label>
                                        <input id="no_of_days" type="number" name="no_of_days">
                                    </div>

                                    <div class="employee-detail-fields flex flex-column">
                                        <label for="total_hours_worked">Total Hours Worked</label>
                                        <input id="total_hours_worked" type="number" name="total_hours_worked"
                                            step=".01">
                                    </div>
                                </div>
                            </div>


                            <div class="flex flex-row gap-20 space-between">
                                <div class="employee-detail-fields flex flex-column">
                                    <label for="overtime_count">Overtime Count</label>
                                    <input id="overtime_count" type="number" name="overtime_count" step=".01">
                                </div>

                                <div class="employee-detail-fields flex flex-column">
                                    <label for="overtime_total_hours">Overtime Total Hours</label>
                                    <input id="overtime_total_hours" type="number" name="overtime_total_hours"
                                        step=".01">
                                </div>
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

                            <div class="flex flex-row gap-20 space-between">
                                <div class="employee-detail-fields flex flex-column" style="width: 30%;">
                                    <label for="work_pay">Work Pay</label>
                                    <input id="work_pay" type="number" name="work_pay" step=".01">
                                </div>

                                <div class="employee-detail-fields flex flex-column" style="width: 30%;">
                                    <label for="overtime_pay">Overtime Pay</label>
                                    <input id="overtime_pay" type="number" name="overtime_pay" step=".01">
                                </div>
                                <div class="employee-detail-fields flex flex-column" style="width: 30%;">
                                    <label for="leave_pay">Leave Pay</label>
                                    <input id="leave_pay" type="number" name="leave_pay" step=".01">
                                </div>
                            </div>

                        </div>

                        <div class="flex flex-column ">
                            <div class="flex flex-column font-medium align-center"
                                style="background-color: var(--color-base-200); border-radius: 8px;padding: 10px">
                                <div style="margin-top: 10px;" class="">
                                    <p style="margin:0px" class="font-bold">Deductions</p>
                                    <div class="flex flex-column flex-wrap">
                                        <div class="employee-detail-fields flex flex-column">
                                            <label for="sss_deduction">SSS</label>
                                            <input id="sss_deduction" type="number" name="sss_deduction" step=".01">
                                        </div>

                                        <div class="employee-detail-fields flex flex-column">
                                            <label for="philhealth_deduction">Philhealth</label>
                                            <input id="philhealth_deduction" type="number" name="philhealth_deduction"
                                                step=".01">
                                        </div>

                                        <div class="employee-detail-fields flex flex-column">
                                            <label for="pagibig_deduction">Pagibig</label>
                                            <input id="pagibig_deduction" type="number" name="pagibig_deduction"
                                                step=".01">
                                        </div>

                                        <div class="employee-detail-fields flex flex-column">
                                            <label for="tax_deduction">Tax</label>
                                            <input id="tax_deduction" type="number" name="tax_deduction" step=".01">
                                        </div>

                                        <div class="employee-detail-fields flex flex-column">
                                            <label for="other_deduction">Other Deduction</label>
                                            <input id="other_deduction" type="number" name="other_deduction" step=".01"
                                                value="0">
                                        </div>
                                    </div>
                                </div>

                                <div style="margin-top: 6px;">
                                    <div class="employee-detail-fields flex flex-column">
                                        <label for="total_deduction" class="font-bold">Total Deduction</label>
                                        <input id="total_deduction" type="number" name="total_deduction" step=".01"
                                            readonly>
                                    </div>
                                    <div class="employee-detail-fields flex flex-column">
                                        <label for="net_pay" class="font-bold">Net Pay</label>
                                        <input id="net_pay" type="number" name="net_pay" step=".01" readonly>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>
            </div>
        </form>
    </dialog>

    <dialog id="add_payroll_modal" style="border-radius: 10px; border: 1px solid #D1D1D1;">
        <form action="payroll.php" method="POST" class="flex flex-column">
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

            </div>
            <div id="modal-content" class="employee-detail-container flex flex-column justify-center">

                <div class="flex flex-column justify-center gap-20 font-medium">
                    <div>
                        <p class="text-capitalize font-bold" style="font-size: 32px;margin: 0">Create Payroll</p>
                    </div>
                    <div class="flex flex-row space-evenly gap-20">

                        <div class="employee-detail-fields" style="display: none">
                            <label for="create_employee_id">Employee ID</label>
                            <input id="create_employee_id" type="text" name="create_employee_id" readonly>
                        </div>

                        <div class="employee-detail-fields">
                            <label for="employee-search">Employee<span style="color: red">*</span></label>
                            <input type="text" id="employee-search" name="create_employee"
                                placeholder="Type employee name..." required autocomplete="off">
                            <div id="employee-results" class="dropdown-content"></div>
                        </div>
                    </div>
                </div>


                <div class="flex flex-column space-between gap-20 font-medium">
                    <div class="flex flex-row space-between gap-20">

                        <div class="flex flex-column">
                            <div class="flex flex-row gap-20 space-between">
                                <div class="employee-detail-fields flex flex-column">
                                    <label for="create_job_name">Job</label>
                                    <input id="create_job_name" type="text" name="create_job_name" readonly>
                                </div>

                                <div class="employee-detail-fields flex flex-column">
                                    <label for="create_department_name">Department</label>
                                    <input id="create_department_name" type="text" name="create_department_name"
                                        readonly>
                                </div>
                            </div>

                            <div class="flex flex-row gap-20 space-between">
                                <div class="employee-detail-fields flex flex-column">
                                    <label for="create_salary">Base Salary</label>
                                    <input id="create_salary" type="text" name="create_salary" readonly>
                                </div>

                                <div class="employee-detail-fields flex flex-column">
                                    <label for="create_bonus">bonus<span style="color: red">*</span></label>
                                    <input id="create_bonus" type="number" name="create_bonus" value="0" step=".01"
                                        required>
                                </div>
                            </div>

                            <div class="flex flex-row gap-20 space-between">
                                <div class="employee-detail-fields flex flex-column">
                                    <label for="create_payroll_start_date">Start Date</label>
                                    <input id="create_payroll_start_date" type="date" name="create_payroll_start_date"
                                        value="2025-03-01">
                                </div>

                                <div class="employee-detail-fields flex flex-column">
                                    <label for="create_payroll_end_date">End Date</label>
                                    <input id="create_payroll_end_date" type="date" name="create_payroll_end_date"
                                        value="2025-05-15">
                                </div>
                            </div>

                            <div style="padding: 10px 0px;">
                                <div class="flex flex-row gap-20 space-between">
                                    <p style="margin:0px" class="font-bold">Regular</p>
                                    <button id="fetch-attendance-btn" type="button"
                                        style="padding: 10px; font-size: 12px;">Calculate
                                        Payroll</button>
                                </div>
                                <div class="flex flex-row gap-20 space-between">
                                    <div class="employee-detail-fields flex flex-column">
                                        <label for="create_no_of_days">No of Days</label>
                                        <input id="create_no_of_days" type="number" name="create_no_of_days">
                                    </div>

                                    <div class="employee-detail-fields flex flex-column">
                                        <label for="create_total_hours_worked">Total Hours Worked</label>
                                        <input id="create_total_hours_worked" type="number"
                                            name="create_total_hours_worked" step=".01">
                                    </div>
                                </div>
                            </div>


                            <div class="flex flex-row gap-20 space-between">
                                <div class="employee-detail-fields flex flex-column">
                                    <label for="create_overtime_count">Overtime Count</label>
                                    <input id="create_overtime_count" type="number" name="create_overtime_count"
                                        step=".01">
                                </div>

                                <div class="employee-detail-fields flex flex-column">
                                    <label for="create_overtime_total_hours">Overtime Total Hours</label>
                                    <input id="create_overtime_total_hours" type="number"
                                        name="create_overtime_total_hours" step=".01">
                                </div>
                            </div>

                            <div class="flex flex-row gap-20 space-between">
                                <div class="employee-detail-fields flex flex-column">
                                    <label for="create_leave_count">Leave Count</label>
                                    <input id="create_leave_count" type="number" name="create_leave_count">
                                </div>

                                <div class="employee-detail-fields flex flex-column">
                                    <label for="create_leave_total_days">Leave Total Days</label>
                                    <input id="create_leave_total_days" type="number" name="create_leave_total_days">
                                </div>
                            </div>

                            <div class="flex flex-row gap-20 space-between">
                                <div class="employee-detail-fields flex flex-column" style="width: 30%;">
                                    <label for="create_work_pay">Work Pay</label>
                                    <input id="create_work_pay" type="number" name="create_work_pay" step=".01">
                                </div>

                                <div class="employee-detail-fields flex flex-column" style="width: 30%;">
                                    <label for="create_overtime_pay">Overtime Pay</label>
                                    <input id="create_overtime_pay" type="number" name="create_overtime_pay" step=".01">
                                </div>
                                <div class="employee-detail-fields flex flex-column" style="width: 30%;">
                                    <label for="create_leave_pay">Leave Pay</label>
                                    <input id="create_leave_pay" type="number" name="create_leave_pay" step=".01">
                                </div>
                            </div>

                        </div>

                        <div class="flex flex-column ">
                            <div class="flex flex-column font-medium align-center"
                                style="background-color: var(--color-base-200); border-radius: 8px;padding: 10px">
                                <div style="margin-top: 10px;" class="">
                                    <p style="margin:0px" class="font-bold">Deductions</p>
                                    <div class="flex flex-column flex-wrap">
                                        <div class="employee-detail-fields flex flex-column">
                                            <label for="create_sss_deduction">SSS</label>
                                            <input id="create_sss_deduction" type="number" name="create_sss_deduction"
                                                step=".01">
                                        </div>

                                        <div class="employee-detail-fields flex flex-column">
                                            <label for="create_philhealth_deduction">Philhealth</label>
                                            <input id="create_philhealth_deduction" type="number"
                                                name="create_philhealth_deduction" step=".01">
                                        </div>

                                        <div class="employee-detail-fields flex flex-column">
                                            <label for="create_pagibig_deduction">Pagibig</label>
                                            <input id="create_pagibig_deduction" type="number"
                                                name="create_pagibig_deduction" step=".01">
                                        </div>

                                        <div class="employee-detail-fields flex flex-column">
                                            <label for="create_tax_deduction">Tax</label>
                                            <input id="create_tax_deduction" type="number" name="create_tax_deduction"
                                                step=".01">
                                        </div>

                                        <div class="employee-detail-fields flex flex-column">
                                            <label for="create_other_deduction">Other Deduction</label>
                                            <input id="create_other_deduction" type="number"
                                                name="create_other_deduction" step=".01" value="0">
                                        </div>
                                    </div>
                                </div>

                                <div style="margin-top: 6px;">
                                    <div class="employee-detail-fields flex flex-column">
                                        <label for="create_total_deduction" class="font-bold">Total Deduction</label>
                                        <input id="create_total_deduction" type="number" name="create_total_deduction"
                                            step=".01" readonly>
                                    </div>
                                    <div class="employee-detail-fields flex flex-column">
                                        <label for="create_net_pay" class="font-bold">Net Pay</label>
                                        <input id="create_net_pay" type="number" name="create_net_pay" step=".01"
                                            readonly>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>
            </div>

            <div class="employee-detail-edit-button-container">

                <div>
                    <button class="employee-detail-edit-button" type="submit" value="create_payroll"
                        name="create_payroll">Create
                        Payroll</button>
                </div>



            </div>
        </form>
    </dialog>

</body>
<script>
    document.addEventListener("DOMContentLoaded", () => {

        const form = document.getElementById("payrollForm");
        const startInput = document.getElementById("payroll_start");
        const endInput = document.getElementById("payroll_end_date");

        function autoSubmit() {
            const startValue = startInput.value;
            const endValue = endInput.value;

            if (startValue && endValue) {
                const startDate = new Date(startValue);
                const endDate = new Date(endValue);

                if (endDate >= startDate) {

                    form.submit();
                } else {
                    alert("Error: The end date must be later than or equal to the start date!");
                    // Optionally reset end date to a valid value:
                    // endInput.value = startValue;
                }
            }
        }

        startInput.addEventListener("change", autoSubmit);
        endInput.addEventListener("change", autoSubmit);

        const addPayrollModal = document.getElementById('add_payroll_modal');
        const payrollDetails = document.getElementById('payroll_details');
        const employeeSearch = document.getElementById("employee-search");
        const fetchAttendanceBtn = document.getElementById('fetch-attendance-btn');
        const resultsDiv = document.getElementById("employee-results");

        if (!addPayrollModal) {
            console.error("Element with ID 'add_payroll_modal' not found.");
            return;
        }

        const openAddEmployeeModal = () => {
            addPayrollModal.showModal();
        };

        const closeAddEmployeeModal = () => {
            addPayrollModal.close();
        };

        addPayrollModal.addEventListener('click', (e) => {
            if (e.target === addPayrollModal) {
                closeAddEmployeeModal();
            }
        });


        window.openAddEmployeeModal = openAddEmployeeModal;
        window.closeAddEmployeeModal = closeAddEmployeeModal;

        if (employeeSearch) {
            employeeSearch.addEventListener("keyup", function () {
                const query = this.value.trim();
                const resultsDiv = document.getElementById("employee-results");

                if (!resultsDiv) {
                    console.error("Element with ID 'employee-results' not found.");
                    return;
                }

                if (query === "") {
                    resultsDiv.innerHTML = "";
                    resultsDiv.style.display = "none";
                    return;
                }

                console.log("Searching for:", query);
                fetch("includes/search_employee.php?q=" + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        console.log("Returned data:", data);
                        resultsDiv.innerHTML = "";

                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach(employee => {
                                const div = document.createElement("div");
                                div.className = "result-item";
                                div.textContent = `${employee.first_name} ${employee.last_name}`;
                                div.dataset.employeeId = employee.employee_id;

                                div.addEventListener("click", () => {
                                    document.getElementById("employee-search").value = `${employee.first_name} ${employee.last_name}`;
                                    document.getElementById("create_employee_id").value = employee.employee_id;
                                    document.getElementById("create_job_name").value = employee.job_name;
                                    document.getElementById("create_department_name").value = employee.department_name;
                                    document.getElementById("create_salary").value = employee.salary;
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
                        console.error("Error fetching employee data:", error);
                        resultsDiv.style.display = "none";
                    });
            });
        } else {
            console.error("Employee search element not found.");
        }

        if (fetchAttendanceBtn) {
            fetchAttendanceBtn.addEventListener('click', async (event) => {
                event.preventDefault();

                const employeeIdEl = document.getElementById('create_employee_id');
                const startDateEl = document.getElementById('create_payroll_start_date');
                const endDateEl = document.getElementById('create_payroll_end_date');
                const salaryEl = document.getElementById('create_salary');

                const employeeId = employeeIdEl ? employeeIdEl.value : '';
                const start_date = startDateEl ? startDateEl.value : '';
                const end_date = endDateEl ? endDateEl.value : '';

                if (!employeeId) {
                    alert("Employee ID is missing. Please select an employee.");
                    return;
                }
                if (!start_date) {
                    alert("Start Date is missing. Please select a start date.");
                    return;
                }
                if (!end_date) {
                    alert("End Date is missing. Please select an end date.");
                    return;
                }

                const attendanceUrl = `includes/attendance_query.php?employee_id=${encodeURIComponent(employeeId)}&start_date=${encodeURIComponent(start_date)}&end_date=${encodeURIComponent(end_date)}`;
                const overtimeUrl = `includes/overtime_query.php?employee_id=${encodeURIComponent(employeeId)}&start_date=${encodeURIComponent(start_date)}&end_date=${encodeURIComponent(end_date)}`;
                const leaveUrl = `includes/leave_query.php?employee_id=${encodeURIComponent(employeeId)}&start_date=${encodeURIComponent(start_date)}&end_date=${encodeURIComponent(end_date)}`;
                const rateUrl = 'includes/fetch_rates.php';

                try {
                    const [attendanceData, overtimeData, leaveData, rateData] = await Promise.all([
                        fetch(attendanceUrl).then(r => {
                            if (!r.ok) throw new Error('Attendance request failed');
                            return r.json();
                        }),
                        fetch(overtimeUrl).then(r => {
                            if (!r.ok) throw new Error('Overtime request failed');
                            return r.json();
                        }),
                        fetch(leaveUrl).then(r => {
                            if (!r.ok) throw new Error('Leave request failed');
                            return r.json();
                        }),
                        fetch(rateUrl).then(r => {
                            if (!r.ok) throw new Error('Rate request failed');
                            return r.json();
                        })
                    ]);

                    const calculateHourlyRate = () => {
                        const monthlySalary = salaryEl ? parseFloat(salaryEl.value) : 0;
                        const hoursPerWeek = 40;
                        const weeksPerMonth = 4.33;
                        return monthlySalary / (hoursPerWeek * weeksPerMonth) || 0;
                    };

                    const convertPercentage = percentage => percentage / 100;
                    const roundToTwo = num => Math.round(num * 100) / 100;

                    const setVal = (id, value) => {
                        const el = document.getElementById(id);
                        if (el) el.value = value;
                    };

                    setVal('create_no_of_days', attendanceData.count);
                    setVal('create_total_hours_worked', attendanceData.totalHours);
                    setVal('create_overtime_count', overtimeData.count);
                    setVal('create_overtime_total_hours', overtimeData.totalHours);
                    setVal('create_leave_count', leaveData.count);
                    setVal('create_leave_total_days', leaveData.totalDays);

                    const hourlyRate = calculateHourlyRate();
                    const workPay = roundToTwo(attendanceData.totalHours * hourlyRate);
                    setVal('create_work_pay', workPay);

                    const overtimePay = roundToTwo(overtimeData.totalHours * (convertPercentage(rateData.overtime_rate) * hourlyRate));
                    setVal('create_overtime_pay', overtimePay);

                    const leavePay = roundToTwo(leaveData.totalDays * (hourlyRate * 8));
                    setVal('create_leave_pay', leavePay);

                    const subTotalNetPay = workPay + overtimePay + leavePay;

                    const sss_deduction = roundToTwo(subTotalNetPay * convertPercentage(rateData.sss_rate));
                    const philhealth_deduction = roundToTwo(subTotalNetPay * convertPercentage(rateData.philhealth_rate));
                    const pagibig_deduction = roundToTwo(subTotalNetPay * convertPercentage(rateData.pagibig_rate));
                    const tax_deduction = roundToTwo(subTotalNetPay * convertPercentage(rateData.tax));

                    const otherDeductionEl = document.getElementById('create_other_deduction');
                    const other_deduction = otherDeductionEl ? Number(otherDeductionEl.value) : 0;

                    const bonus = parseFloat(document.getElementById('create_bonus').value) || 0;
                    console.log(bonus)

                    const totalDeduction = roundToTwo(sss_deduction + philhealth_deduction + pagibig_deduction + tax_deduction + other_deduction);
                    const netPay = roundToTwo(subTotalNetPay - totalDeduction + bonus);

                    setVal('create_sss_deduction', sss_deduction);
                    setVal('create_philhealth_deduction', philhealth_deduction);
                    setVal('create_pagibig_deduction', pagibig_deduction);
                    setVal('create_tax_deduction', tax_deduction);
                    setVal('create_total_deduction', totalDeduction);
                    setVal('create_net_pay', netPay);

                } catch (error) {
                    console.error("Error fetching payroll data:", error);
                }
            });
        } else {
            console.error("Fetch attendance button element not found.");
        }

        window.openPayrollModal = function (id) {
            if (payrollDetails) {
                const currentRow = document.querySelector(`#row-${id}`);

                let data;
                try {
                    data = JSON.parse(currentRow.dataset.user);
                } catch (error) {
                    console.error("Error parsing JSON data from row dataset:", error);
                    return;
                }

                Object.keys(data).forEach(key => {
                    const inputElement = document.querySelector(`input[name="${key}"]`);
                    if (inputElement) {
                        inputElement.value = data[key];
                    }

                    const employeeName = document.querySelector('input[name=employee_name]');
                    employeeName.value = data.first_name + " " + data.last_name;
                });


                payrollDetails.showModal();
            }
        };

        window.closePayrollModal = function () {
            if (payrollDetails) {
                payrollDetails.close();
            } else {
                console.error("Payroll modal element not found.");
            }
        };

        if (payrollDetails) {
            payrollDetails.addEventListener('click', (e) => {
                if (e.target === payrollDetails) {
                    closePayrollModal();
                }
            });
        }
    });
</script>


</html>