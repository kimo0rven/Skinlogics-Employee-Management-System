<?php
session_start();

if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

$user_account_id = (int) $_SESSION["user_account_id"];

include 'includes/database.php';
include 'config.php';

function formatDate($rawDate, $format = 'M d, Y h:i A')
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

$overtimeRequests = [];
try {
    $sql_overtime = "SELECT ot.*, e.first_name, e.middle_name, e.last_name
                   FROM overtime ot
                   INNER JOIN employee e ON ot.employee_id = e.employee_id
                   WHERE ot.employee_id = :loggedInId
                   ORDER BY ot.date_created DESC";

    $stmt_overtime = $pdo->prepare($sql_overtime);
    $stmt_overtime->bindValue(':loggedInId', $_SESSION['employee_id'], PDO::PARAM_INT);
    $stmt_overtime->execute();
    $overtimeRequests = $stmt_overtime->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching overtime data: " . $e->getMessage();
}

$searchQuery = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchQuery = trim($_GET['search']);
    try {
        $sql_search = "SELECT ot.*, e.first_name, e.middle_name, e.last_name
                       FROM overtime ot
                       INNER JOIN employee e ON ot.employee_id = e.employee_id
                       WHERE ot.employee_id = :loggedInId
                       AND (
                           e.first_name LIKE :searchQuery 
                           OR e.middle_name LIKE :searchQuery 
                           OR e.last_name LIKE :searchQuery 
                           OR ot.ot_type LIKE :searchQuery 
                           OR ot.status LIKE :searchQuery
                       )
                       ORDER BY lr.date_created DESC";
        $stmt_search = $pdo->prepare($sql_search);
        $stmt_search->bindParam(':loggedInId', $_SESSION['employee_id'], PDO::PARAM_INT);
        $searchTerm = "%$searchQuery%";
        $stmt_search->bindParam(':searchQuery', $searchTerm, PDO::PARAM_STR);
        $stmt_search->execute();
        $overtimeRequests = $stmt_search->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        die("An error occurred. Please try again later.");
    }
}

function calculateHoursDifference($start_time, $end_time)
{
    $start = new DateTime($start_time);
    $end = new DateTime($end_time);

    $interval = $start->diff($end);
    $hours = $interval->h + ($interval->days * 24) + ($interval->i / 60);

    return round($hours, 2);
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Admin'; ?> | Overtime Requests</title>
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
                            <h1>OVERTIME REQUESTS</h1>
                        </div>
                        <div id="logout-admin" class="dashboard-content-header font-medium">
                            <?php include('includes/header-avatar.php') ?>
                        </div>
                    </div>

                    <div class="employee-main-content">
                        <div class="employee-header">
                            <div class="employee-header-div">
                                <form method="GET" action="overtime.php">

                                    <input type="text" id="employee_search" type="employee_search" name="search"
                                        placeholder="Search overtime requests"
                                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                                    <button type="submit" style="margin: 0px">
                                        <img class="img-resize" height="32px" width="32px"
                                            src="assets/images/icons/search-icon.png" alt="Search">
                                    </button>
                                    <div> <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                                            <a href="overtime.php" class="clear-search">Clear Search</a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>

                        </div>

                        <div class="employee-display" style="overflow: auto;">
                            <table class="employee-table">
                                <thead class="font-bold">
                                    <tr>

                                        <th>Type</th>
                                        <th>Start Date & Time</th>
                                        <th>End Date & Time</th>
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
                                    <?php if (!empty($overtimeRequests)): ?>
                                        <?php foreach ($overtimeRequests as $overtimeRequest): ?>
                                            <tr id="row-<?php echo $overtimeRequest['employee_id'] ?>"
                                                style="white-space: nowrap;"
                                                data-user='<?php echo json_encode($overtimeRequest); ?>'
                                                class="employee-display-list text-center">
                                                <!-- <td style="text-align: center;">
                                                    <?php echo $overtimeRequest['first_name'] . " " . $overtimeRequest['last_name'] ?>
                                                </td> -->
                                                <td>
                                                    <?php echo htmlspecialchars($overtimeRequest['ot_type']); ?>
                                                </td>
                                                <td style="text-align: center;">
                                                    <?php echo formatDate($overtimeRequest['start_time']); ?>
                                                </td>
                                                <td style="text-align: center;">
                                                    <?php echo formatDate($overtimeRequest['end_time']); ?>
                                                </td>
                                                <td style="text-align: center;">
                                                    <?php
                                                    echo calculateHoursDifference($overtimeRequest['start_time'], $overtimeRequest['end_time']) . " hours";
                                                    ?>
                                                <td><?php echo htmlspecialchars($overtimeRequest['ot_reason']); ?></td>
                                                <td style="text-align: center;">
                                                    <?php
                                                    $tl_status = htmlspecialchars($overtimeRequest['tl_approval']);
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
                                                    $rawDate = $overtimeRequest['tl_approval_date'];
                                                    echo formatDate($rawDate);
                                                    ?>
                                                </td>

                                                <td style="text-align: center;">
                                                    <?php
                                                    $hr_manager = htmlspecialchars($overtimeRequest['hr_manager_approval']);
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
                                                    $rawDate = $overtimeRequest['hr_manager_approval_date'];
                                                    echo formatDate($rawDate);
                                                    ?>
                                                </td>

                                                </td>
                                                <td style="text-align: center;">
                                                    <?php
                                                    $status = htmlspecialchars($overtimeRequest['status']);
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
                                            <td colspan="9">No overtime requests found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>


        <script>


            function formatDate(dateStr) {
                if (!dateStr) return "N/A";
                const date = new Date(dateStr);
                return date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
            }

            function calcDuration(start, end) {
                const s = new Date(start);
                const e = new Date(end);
                return ((e - s) / (1000 * 60 * 60 * 24)) + 1;
            }
        </script>
</body>

</html>