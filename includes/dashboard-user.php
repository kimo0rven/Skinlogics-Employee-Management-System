<?php
$message = '';

try {
    $sql = "SELECT count(*) FROM leave_request WHERE employee_id = :employee_id AND status = 'PENDING'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':employee_id' => $_SESSION['employee_id']]);
    $leaveReq = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $pendingLeaveCount = $leaveReq[0]['count(*)'];

} catch (Exception $e) {
    die("" . $e->getMessage());
}

try {
    $sql = "SELECT count(*) FROM overtime WHERE employee_id = :employee_id AND status = 'PENDING'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':employee_id' => $_SESSION['employee_id']]);
    $overtimeReq = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $pendingOvertimeCount = $overtimeReq[0]['count(*)'];

} catch (Exception $e) {
    die("" . $e->getMessage());
}

try {
    $employeeId = $_SESSION['employee_id'];

    $sql = "
    SELECT
    SUM(CASE WHEN status = 'LATE' THEN 1 ELSE 0 END) AS late_count,
    SUM(CASE WHEN status = 'PRESENT' THEN 1 ELSE 0 END) AS present_count,
    SUM(CASE WHEN status = 'ABSENT' THEN 1 ELSE 0 END) AS absent_count
    FROM attendance
    WHERE employee_id = :employee_id
    AND YEAR(date_created) = YEAR(CURDATE())
    AND MONTH(date_created) = MONTH(CURDATE())
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':employee_id' => $employeeId]);
    $attendanceSummary = $stmt->fetch(PDO::FETCH_ASSOC);

    $lateCount = $attendanceSummary['late_count'];
    $presentCount = $attendanceSummary['present_count'];
    $absentCount = $attendanceSummary['absent_count'];

} catch (Exception $e) {
    die("Error fetching attendance summary: " . $e->getMessage());
}

try {
    $stmt = $pdo->prepare("
        WITH RECURSIVE MonthSeries AS (
            SELECT
                1 AS month_num,
                DATE_FORMAT(CURDATE(), '%Y-01-01') AS month_start
            UNION ALL
            SELECT
                month_num + 1,
                DATE_ADD(month_start, INTERVAL 1 MONTH)
            FROM
                MonthSeries
            WHERE
                month_num < MONTH(CURDATE())
        )
        SELECT
            DATE_FORMAT(ms.month_start, '%M') AS month_name,
            SUM(p.net_pay) AS total_net_pay
        FROM
            MonthSeries ms
        LEFT JOIN
            payroll p ON YEAR(p.payroll_start_date) = YEAR(ms.month_start)
            AND MONTH(p.payroll_start_date) = MONTH(ms.month_start)
            AND p.employee_id = :employee_id
        GROUP BY
            month_name
        ORDER BY
            ms.month_start
    ");
    $stmt->bindParam(':employee_id', $_SESSION['employee_id'], PDO::PARAM_INT);
    $stmt->execute();

    $employeeNetPayPerMonth = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $employeeNetPayPerMonth[] = $row['total_net_pay'];
    }

    // print_r($employeeNetPayPerMonth);

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

?>


<div class="dashboard-content-item2">
    <div class="dashboard-main-content" style="flex-basis: 100%">
        <div class="dashboard-kpi font-medium">
            <div class="kpi-item">
                <p class="kpi-label">Pending Vacation Requests</p>
                <div class="kpi-value-container">
                    <p class="kpi-value"><?php echo $pendingLeaveCount; ?></p>
                    <img src="./assets/images/employee.png" class="kpi-icon" alt="Employee Icon">
                </div>
            </div>
            <div class="kpi-item">
                <p class="kpi-label">Pending Overtime Requests</p>
                <div class="kpi-value-container">
                    <p class="kpi-value"><?php echo $pendingOvertimeCount; ?></p>
                    <img src="./assets/images/active.png" class="kpi-icon" alt="Employee Icon">
                </div>
            </div>
            <div class="kpi-item">
                <p class="kpi-label">Presents This Month</p>
                <div class="kpi-value-container">
                    <p class="kpi-value"><?php echo $presentCount; ?></p>
                    <img src="./assets/images/resign.png" class="kpi-icon" alt="Employee Icon">
                </div>
            </div>
            <div class="kpi-item">
                <p class="kpi-label">Late This Month</p>
                <div class="kpi-value-container">
                    <p class="kpi-value"><?php echo $lateCount; ?></p>
                    <img src="./assets/images/terminated.png" class="kpi-icon" alt="Employee Icon">
                </div>
            </div>

            <div class="kpi-item">
                <p class="kpi-label">Absents This Month</p>
                <div class="kpi-value-container">
                    <p class="kpi-value"><?php echo $absentCount; ?></p>
                    <img src="./assets/images/terminated.png" class="kpi-icon" alt="Employee Icon">
                </div>
            </div>
        </div>
        <div style="height: 140%" class="dashboard-chart">

            <div style="height: 100%;" style="padding: 20px;"
                class="class-container chart-size flex flex-row space-evenly align-center gap-20">
                <canvas id="employeePayrollChart"></canvas>
            </div>
        </div>
    </div>
    <!-- <div class="dashboard-other-info border-red">

        1
    </div> -->
</div>

<script>
    const employeePayrollChart = document.getElementById('employeePayrollChart');
    new Chart(employeePayrollChart, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Total Payroll Each Month',
                data: <?php echo json_encode(array_values($employeeNetPayPerMonth)) ?>,
                borderWidth: 1,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(199, 199, 199, 0.8)',
                    'rgba(102, 205, 170, 0.8)',
                    'rgba(255, 160, 122, 0.8)',
                    'rgba(60, 179, 113, 0.8)',
                    'rgba(255, 105, 180, 0.8)',
                    'rgba(173, 216, 230, 0.8)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(199, 199, 199, 1)',
                    'rgba(102, 205, 170, 1)',
                    'rgba(255, 160, 122, 1)',
                    'rgba(60, 179, 113, 1)',
                    'rgba(255, 105, 180, 1)',
                    'rgba(173, 216, 230, 1)'
                ]
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                }
            }
        }
    });
</script>