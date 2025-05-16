<?php
if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}

include 'includes/database.php';
include 'config.php';

try {
    $stmt = $pdo->prepare("SELECT
    COUNT(*) AS total_employees,
    SUM(CASE WHEN gender = 'Male' THEN 1 ELSE 0 END) AS total_males,
    SUM(CASE WHEN gender = 'Female' THEN 1 ELSE 0 END) AS total_females,
    SUM(CASE WHEN gender = 'Other' THEN 1 ELSE 0 END) AS total_others
FROM
    employee;");
    $stmt->execute();
    $employeeByGender = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
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
        GROUP BY
            month_name
        ORDER BY
            ms.month_start
    ");
    $stmt->execute();

    $netPayPerMonth = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $netPayPerMonth[] = $row['total_net_pay'];
    }

    // print_r($netPayPerMonth);

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

try {

    $stmt = $pdo->prepare("SELECT * FROM employee WHERE user_account_id = :user_account_id LIMIT 1");
    $stmt->execute([':user_account_id' => $user_account_id]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($employee) {
        $first_name = $employee['first_name'];
        $last_name = $employee['last_name'];
    } else {
        echo "No records found for user account ID: $user_account_id";
    }


    $stmt = $pdo->query("
        SELECT 
            COUNT(*) AS total_records,
            SUM(status = 'Active') AS total_active,
            SUM(status = 'Resigned') AS total_resigned,
            SUM(status = 'Terminated') AS total_terminated
        FROM employee
    ");
    $counts = $stmt->fetch(PDO::FETCH_ASSOC);
    $employeeCount = $counts['total_records'];
    $activeCount = $counts['total_active'];
    $resignedCount = $counts['total_resigned'];
    $terminatedCount = $counts['total_terminated'];


    $today = new DateTime('now', new DateTimeZone('Asia/Manila'));
    $currentDate = $today->format('Y-m-d');


    $stmt = $pdo->prepare("
        SELECT status, COUNT(*) AS count 
        FROM attendance 
        WHERE date_created = :date 
        GROUP BY status
    ");
    $stmt->execute([':date' => $currentDate]);
    $attendanceData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $statusCounts = ['Present' => 0, 'Absent' => 0, 'Late' => 0, 'On Leave' => 0];
    foreach ($attendanceData as $row) {
        if (isset($statusCounts[$row['status']])) {
            $statusCounts[$row['status']] = $row['count'];
        }
    }


    $currentMonthDay = $today->format('m-d');
    $stmt = $pdo->prepare("
        SELECT e.first_name, e.last_name, e.user_account_id, u.avatar
        FROM employee e
        JOIN user_account u ON e.user_account_id = u.user_account_id
        WHERE DATE_FORMAT(e.dob, '%m-%d') = :currentMonthDay
    ");
    $stmt->execute([':currentMonthDay' => $currentMonthDay]);
    $employeesWithBirthdaysToday = $stmt->fetchAll(PDO::FETCH_ASSOC);


    $weekStart = (clone $today)->modify('last sunday');
    $weekEnd = (clone $weekStart)->modify('+6 days');
    $weekStartDate = $weekStart->format('Y-m-d');
    $weekEndDate = $weekEnd->format('Y-m-d');


    $stmt = $pdo->prepare("
        SELECT lr.employee_id, e.first_name, e.last_name, e.user_account_id, ua.avatar,
               lr.start_date, lr.end_date, lr.reason
        FROM leave_request lr
        JOIN employee e ON lr.employee_id = e.employee_id
        JOIN user_account ua ON e.user_account_id = ua.user_account_id
        WHERE lr.start_date <= :week_end 
          AND lr.end_date >= :week_start 
          AND lr.tl_approval = 'Approved'
          AND lr.hr_manager_approval = 'Approved'
        ORDER BY lr.start_date DESC
    ");
    $stmt->execute([
        ':week_start' => $weekStartDate,
        ':week_end' => $weekEndDate
    ]);
    $weeklyLeaves = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

try {
    $sql = "
    WITH RECURSIVE MonthSeries AS (
        SELECT
            1 AS month_num,
            DATE_FORMAT(CURDATE(), '%Y-01-01') AS month_start
        UNION ALL
        SELECT
            month_num + 1,
            DATE_ADD(month_start, INTERVAL 1 MONTH)
        FROM MonthSeries
        WHERE month_num < MONTH(CURDATE())
    )
    SELECT
        DATE_FORMAT(ms.month_start, '%M') AS month_name,
        SUM(CASE WHEN at.status = 'Present' THEN 1 ELSE 0 END) AS total_present,
        SUM(CASE WHEN at.status = 'Absent' THEN 1 ELSE 0 END) AS total_absent,
        SUM(CASE WHEN at.status = 'Late' THEN 1 ELSE 0 END) AS total_late
    FROM MonthSeries ms
    LEFT JOIN attendance at ON YEAR(at.date_created) = YEAR(ms.month_start)
        AND MONTH(at.date_created) = MONTH(ms.month_start)
    GROUP BY month_name
    ORDER BY ms.month_start;
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Prepare separate arrays for each type of attendance
    $presentTotals = [];
    $absentTotals = [];
    $lateTotals = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $presentTotals[] = $row['total_present'];
        $absentTotals[] = $row['total_absent'];
        $lateTotals[] = $row['total_late'];
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>

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
        <div style="min-height: 140%;" class="dashboard-chart flex flex-row align-center justify-center">
            <div style="height: 10%;" style="padding: 20px;"
                class="class-container chart-size flex flex-row space-evenly align-center gap-20">
                <canvas id="myChart"></canvas>
                <canvas style="height: 10%;" id="genderChart"></canvas>

            </div>

            <!-- <div style="height: 10%;" style="padding: 20px;"
                class="class-container chart-size flex flex-row space-evenly gap-20">
                <canvas id="presenteChart"></canvas>
                <canvas id="lateChart"></canvas>
                <canvas id="absentChart"></canvas>
            </div> -->

        </div>
    </div>
    <div class="dashboard-other-info">

        <div class="card attendance-container font-medium">
            <div>
                <h4>Present</h4>
                <p><?php echo $statusCounts['Present'] ?></p>
            </div>
            <div>
                <h4>Late</h4>
                <p><?php echo $statusCounts['Late'] ?></p>
            </div>
            <div>
                <h4>Absent</h4>
                <p><?php echo $statusCounts['Absent'] ?></p>
            </div>

        </div>
        <div class="today-events-container">
            <div class="font-bold">
                <div>
                    Today's Event
                </div>
            </div><?php if ($employeesWithBirthdaysToday) {
                foreach ($employeesWithBirthdaysToday as $employee) {
                    $avatar = !empty($employee['avatar']) ? $employee['avatar'] : 'default-avatar.png';
                    ?>
                    <div class="card today-event-layout font-medium">
                        <div>
                            <img class="img-resize" src="assets/images/avatars/<?php echo htmlspecialchars($avatar); ?>"
                                alt="<?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>">
                        </div>
                        <div class="today-event-details">

                            <div class="font-regular">
                                <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>
                            </div>

                            <div class="font-regular">
                                Her birthday is today
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<div class=" no-events">
                                <div class="today-event-details font-medium">
                                    <div>No birthdays today</div>
                                </div></div>';
            } ?>
        </div>

        <div class="card today-on-leave-container  font-medium">
            <div class="font-bold">
                <div>Employees On Leave This Week</div>
            </div>
            <?php if (empty($weeklyLeaves)): ?>
                <div class="today-on-leave-details">
                    <div>No employees on leave This Week</div>
                </div>
            <?php else: ?>
                <?php foreach ($weeklyLeaves as $employee): ?>
                    <?php
                    $startDate = new DateTime($employee['start_date']);
                    $endDate = new DateTime($employee['end_date']);
                    $today = new DateTime();

                    if ($startDate->format('Y-m-d') == $endDate->format('Y-m-d')) {
                        $dateRange = $startDate->format('M j');

                        if ($startDate->format('Y-m-d') == $today->format('Y-m-d')) {
                            $dateRange = 'Only Today';
                        }
                    } else {
                        $dateRange = $startDate->format('M j') . ' - ' . $endDate->format('M j');
                    }
                    $avatar = !empty($employee['avatar']) ? 'assets/images/avatars/' . $employee['avatar'] : 'assets/images/avatars/default.jpg';
                    ?>
                    <div class="today-on-leave-details">
                        <div class="image-resize">
                            <img class="img-resize" src="<?= htmlspecialchars($avatar) ?>" alt="">
                        </div>
                        <div class="today-on-leave-details2">
                            <div class="today-on-leave-details3">
                                <div>
                                    <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?>
                                </div>
                                <div class="today-on-leave-details-date"><?= $dateRange ?></div>
                            </div>
                            <div class="today-on-leave-details-reason">
                                <?= htmlspecialchars($employee['reason']) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('myChart');
    const genderChart = document.getElementById('genderChart');
    const present = document.getElementById('presenteChart');
    const late = document.getElementById('lateChart');
    const absent = document.getElementById('absentChart');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Total Payroll Each Month',
                data: <?php echo json_encode($netPayPerMonth) ?>,
                borderWidth: 1
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

    new Chart(genderChart, {
        type: 'doughnut',
        data: {
            labels: ['Male', 'Female', 'Other'],
            datasets: [{
                label: 'Gender Distribution',
                data: [<?php echo $employeeByGender['total_males']; ?>,
                    <?php echo $employeeByGender['total_females']; ?>,
                    <?php echo $employeeByGender['total_others']; ?>,],
                backgroundColor: [
                    'rgba(0, 123, 255, 0.7)',
                    'rgba(255, 105, 180, 0.7)',
                    'rgba(46, 139, 87, 0.7)',
                ],
                hoverOffset: 4,
            }]
        },
        options: {
            scales: {
                y: {
                    display: false
                }
            }
        }
    });

    const currentDate = new Date();
    const currentMonth = currentDate.getMonth();
    const attendanceLabels = [];

    for (let i = 0; i <= currentMonth; i++) {
        const monthName = new Intl.DateTimeFormat('en-US', { month: 'long' }).format(new Date(currentDate.getFullYear(), i, 1));
        attendanceLabels.push(monthName);
    }

    new Chart(present, {
        type: 'line',
        data: {
            labels: attendanceLabels,
            datasets: [{
                label: 'Attendance by Present',
                data: <?php echo json_encode($presentTotals) ?>,
                fill: false,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
    });

    new Chart(late, {
        type: 'line',
        data: {
            labels: attendanceLabels,
            datasets: [{
                label: 'Attendance by Late',
                data: <?php echo json_encode($lateTotals) ?>,
                fill: false,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
    });

    new Chart(late, {
        type: 'line',
        data: {
            labels: attendanceLabels,
            datasets: [{
                label: 'Attendance by Absent',
                data: <?php echo json_encode($absentTotals) ?>,
                fill: false,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
    });
</script>


</html>