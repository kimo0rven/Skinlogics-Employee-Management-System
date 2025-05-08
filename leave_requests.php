<?php
session_start();

if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

$user_account_id = (int) $_SESSION["user_account_id"];

include 'includes/database.php';
include 'config.php';

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

$leaveRequests = [];
try {
    $sql_leaves = "SELECT
                        lr.*,
                        e.first_name,
                        e.middle_name,
                        e.last_name
                   FROM
                        leave_request lr
                   INNER JOIN
                        employee e ON lr.employee_id = e.employee_id
                   WHERE
                        e.employee_id = :loggedInTeamLeaderId
                   ORDER BY
                        lr.date_created DESC";

    $stmt_leaves = $pdo->prepare($sql_leaves);
    $stmt_leaves->bindValue(':loggedInTeamLeaderId', $_SESSION['employee_id'], PDO::PARAM_INT);
    $stmt_leaves->execute();
    $leaveRequests = $stmt_leaves->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching employee data: " . $e->getMessage();
}


?>

<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Admin'; ?> | Leave Requests</title>
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
                            <h1>LEAVE REQUESTS</h1>
                        </div>
                        <div id="logout-admin" class="dashboard-content-header font-medium">
                            <?php include('includes/header-avatar.php') ?>
                        </div>
                    </div>

                    <div class="employee-main-content">
                        <!-- <div class="employee-header">
                            <div class="employee-header-div">
                                <form method="GET" action="tl_leave_requests.php">

                                    <input type="text" id="employee_search" type="employee_search" name="search"
                                        placeholder="Search leave requests"
                                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                                    <button type="submit" style="margin: 0px">
                                        <img class="img-resize" height="32px" width="32px"
                                            src="assets/images/icons/search-icon.png" alt="Search">
                                    </button>
                                    <div> <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                                            <a href="tl_leave_requests.php" class="clear-search">Clear Search</a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>

                        </div> -->

                        <div class="employee-display" style="overflow: auto;">
                            <table class="employee-table">
                                <thead class="font-bold">
                                    <tr>
                                        <th>Employee</th>
                                        <th>Type</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Duration</th>
                                        <th>Reason</th>
                                        <th>TL Approval</th>
                                        <th>Approval Date</th>
                                        <th>HR Manager Approval</th>
                                        <th>Approval Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody class="font-medium">
                                    <?php if (!empty($leaveRequests)): ?>
                                        <?php foreach ($leaveRequests as $leaveRequest): ?>
                                            <tr id="row-<?php echo $leaveRequest['employee_id'] ?>" style="white-space: nowrap;"
                                                data-user='<?php echo json_encode($leaveRequest); ?>'
                                                class="employee-display-list text-center"
                                                onclick="openModal(<?php echo $leaveRequest['employee_id'] ?>)">
                                                <td style="text-align: center;">
                                                    <?php echo $leaveRequest['first_name'] . " " . $leaveRequest['last_name'] ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($leaveRequest['leave_type']); ?>
                                                </td>
                                                <td style="text-align: center;">
                                                    <?php echo formatDate($leaveRequest['start_date']); ?>
                                                </td>
                                                <td style="text-align: center;">
                                                    <?php echo formatDate($leaveRequest['end_date']); ?>
                                                </td>
                                                <td style="text-align: center;">
                                                    <?php
                                                    if (!empty($leaveRequest['start_date']) && !empty($leaveRequest['end_date'])) {
                                                        $startDate = new DateTime($leaveRequest['start_date']);
                                                        $endDate = new DateTime($leaveRequest['end_date']);
                                                        $interval = $startDate->diff($endDate);
                                                        $days = $interval->days + 1;
                                                        echo $days . ' ' . ($days === 1 ? 'day' : 'days');
                                                    } else {
                                                        echo 'N/A';
                                                    }
                                                    ?>
                                                <td><?php echo htmlspecialchars($leaveRequest['reason']); ?></td>
                                                <td style="text-align: center;">
                                                    <?php
                                                    $tl_status = htmlspecialchars($leaveRequest['tl_approval']);
                                                    switch ($tl_status) {
                                                        case 'Approved':
                                                            echo "<div style='width:80px; margin: 0 auto;' class='employee-status employee-status-active'>Approved</div>";
                                                            break;
                                                        case 'Rejected':
                                                            echo "<div style='width:80px; margin: 0 auto;' class='employee-status employee-status-inactive'>Rejected</div>";
                                                            break;
                                                        case 'Pending':
                                                            echo "<div style='width:80px; margin: 0 auto;' class='employee-status employee-status-resigned'>Pending</div>";
                                                            break;
                                                        default:
                                                            echo "<div>Unknown Status</div>";
                                                            break;
                                                    }
                                                    ?>
                                                </td>

                                                <td style="text-align: center;">
                                                    <?php
                                                    $rawDate = $leaveRequest['tl_approval_date'];
                                                    echo formatDate($rawDate);
                                                    ?>
                                                </td>

                                                <td style="text-align: center;">
                                                    <?php
                                                    $hr_manager = htmlspecialchars($leaveRequest['hr_manager_approval']);
                                                    switch ($hr_manager) {
                                                        case 'Approved':
                                                            echo "<div style='width: 80px; margin: 0 auto;' class='employee-status employee-status-active'>Approved</div>";
                                                            break;
                                                        case 'Rejected':
                                                            echo "<div style='width: 80px; margin: 0 auto;' class='employee-status employee-status-inactive'>Rejected</div>";
                                                            break;
                                                        case 'Pending':
                                                            echo "<div style='width: 80px; margin: 0 auto;' class='employee-status employee-status-resigned'>Pending</div>";
                                                            break;
                                                        default:
                                                            echo "<div>Unknown Status</div>";
                                                            break;
                                                    }
                                                    ?>
                                                </td>

                                                <td style="text-align: center;">
                                                    <?php
                                                    $rawDate = $leaveRequest['hr_manager_approval_date'];
                                                    echo formatDate($rawDate);
                                                    ?>
                                                </td>

                                                </td>
                                                <td style="text-align: center;">
                                                    <?php
                                                    $status = htmlspecialchars($leaveRequest['status']);
                                                    switch ($status) {
                                                        case 'Approved':
                                                            echo "<div style='width: 80px; margin: 0 auto;' class='employee-status employee-status-active'>Approved</div>";
                                                            break;
                                                        case 'Rejected':
                                                            echo "<div style='width: 80px; margin: 0 auto;' class='employee-status employee-status-inactive'>Rejected</div>";
                                                            break;
                                                        case 'Pending':
                                                            echo "<div style='width: 80px; margin: 0 auto;' class='employee-status employee-status-resigned'>Pending</div>";
                                                            break;
                                                        default:
                                                            echo "<div>Unknown Status</div>";
                                                            break;
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="flex space-between">No pending requests</td>
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

</body>

<script>


</script>

</html>