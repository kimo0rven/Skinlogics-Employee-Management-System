<?php
if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}

$user_account_id = $_SESSION['user_account_id'];
$accountType = $_SESSION['account_type'];

include 'includes/database.php';
include 'config.php';

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
        <div class="dashboard-chart">
            1
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
                                <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>'s
                                Birthday
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