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
    $sql_leaves = "SELECT lr.*, e.first_name, e.middle_name, e.last_name
                   FROM leave_request lr
                   INNER JOIN employee e ON lr.employee_id = e.employee_id
                   WHERE e.team_leader_id = :loggedInTeamLeaderId
                   ORDER BY lr.date_created DESC";

    $stmt_leaves = $pdo->prepare($sql_leaves);
    $stmt_leaves->bindValue(':loggedInTeamLeaderId', $_SESSION['employee_id'], PDO::PARAM_INT);
    $stmt_leaves->execute();
    $leaveRequests = $stmt_leaves->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching employee data: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approval'])) {
        $sql = "UPDATE leave_request SET tl_approval = :tl_approval, tl_approval_date = NOW() WHERE leave_id = :leave_id";
        $stmt_update = $pdo->prepare($sql);
        $status = ($_POST['approval'] == 'approve') ? 'Approved' : 'Rejected';
        $stmt_update->execute([
            ':tl_approval' => $status,
            ':leave_id' => $_POST['leave_id'],
        ]);
    }
    header("Location: tl_leave_requests.php");
    exit();
}

$searchQuery = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchQuery = trim($_GET['search']);
    try {
        $sql_search = "SELECT lr.*, e.first_name, e.middle_name, e.last_name
                       FROM leave_request lr
                       INNER JOIN employee e ON lr.employee_id = e.employee_id
                       WHERE e.team_leader_id = :loggedInTeamLeaderId
                         AND (e.first_name LIKE :searchQuery OR e.middle_name LIKE :searchQuery OR e.last_name LIKE :searchQuery 
                              OR lr.leave_type LIKE :searchQuery OR lr.status LIKE :searchQuery)
                       ORDER BY lr.date_created DESC";

        $stmt_search = $pdo->prepare($sql_search);
        $stmt_search->bindParam(':loggedInTeamLeaderId', $_SESSION['employee_id'], PDO::PARAM_INT);
        $searchTerm = "%$searchQuery%";
        $stmt_search->bindParam(':searchQuery', $searchTerm, PDO::PARAM_STR);
        $stmt_search->execute();
        $leaveRequests = $stmt_search->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        die("An error occurred. Please try again later.");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Team Leader | Leave Requests</title>
    <link rel="stylesheet" href="style.css"/>
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
</head>
<body class="font-medium">
    <div id="admin">
        <div class="dashboard-background">
            <div class="dashboard-container">
                <div class="dashboard-navigation">
                    <?php include('includes/navigation.php'); ?>
                </div>
                <div class="dashboard-content">
                    <div class="dashboard-content-item1">
                        <div class="dashboard-content-header font-black">
                            <h1>LEAVE REQUESTS</h1>
                        </div>
                        <div id="logout-admin" class="dashboard-content-header font-medium">
                            <?php include('includes/header-avatar.php'); ?>
                        </div>
                    </div>

                    <div class="employee-main-content">
                        <div class="employee-header">
                            <div class="employee-header-div">
                                <form method="GET" action="tl_leave_requests.php">
                                    <input type="text" id="employee_search" name="search"
                                           placeholder="Search leave requests"
                                           value="<?php echo htmlspecialchars($searchQuery); ?>">
                                    <button type="submit" style="margin: 0px">
                                        <img class="img-resize" height="32px" width="32px"
                                             src="assets/images/icons/search-icon.png" alt="Search">
                                    </button>
                                    <?php if (!empty($searchQuery)): ?>
                                        <a href="tl_leave_requests.php" class="clear-search">Clear Search</a>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>

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
                                            <tr id="row-<?php echo $leaveRequest['leave_id']; ?>"
                                                data-user='<?php echo json_encode($leaveRequest); ?>'
                                                class="employee-display-list text-center"
                                                onclick="openModal(<?php echo $leaveRequest['leave_id']; ?>)">
                                                <td><?php echo $leaveRequest['first_name'] . ' ' . $leaveRequest['last_name']; ?></td>
                                                <td><?php echo htmlspecialchars($leaveRequest['leave_type']); ?></td>
                                                <td><?php echo formatDate($leaveRequest['start_date']); ?></td>
                                                <td><?php echo formatDate($leaveRequest['end_date']); ?></td>
                                                <td>
                                                    <?php
                                                    $start = new DateTime($leaveRequest['start_date']);
                                                    $end = new DateTime($leaveRequest['end_date']);
                                                    $interval = $start->diff($end)->days + 1;
                                                    echo "$interval " . ($interval === 1 ? 'day' : 'days');
                                                    ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($leaveRequest['reason']); ?></td>
                                                <td>
                                                    <?php
                                                    $status = $leaveRequest['tl_approval'];
                                                    echo "<div class='employee-status employee-status-" . strtolower($status) . "'>$status</div>";
                                                    ?>
                                                </td>
                                                <td><?php echo formatDate($leaveRequest['tl_approval_date']); ?></td>
                                                <td>
                                                    <?php
                                                    $hr = $leaveRequest['hr_manager_approval'];
                                                    echo "<div class='employee-status employee-status-" . strtolower($hr) . "'>$hr</div>";
                                                    ?>
                                                </td>
                                                <td><?php echo formatDate($leaveRequest['hr_manager_approval_date']); ?></td>
                                                <td>
                                                    <?php
                                                    $overall = $leaveRequest['status'];
                                                    echo "<div class='employee-status employee-status-" . strtolower($overall) . "'>$overall</div>";
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="11">No leave requests found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Dialog -->
        <dialog id="myModal" style="border-radius: 10px; border: 1px solid #D1D1D1;">
            <form action="tl_leave_requests.php" method="POST" class="flex flex-column gap-20">
                <div id="modal_close_btn" onclick="closeModal()" style="position: absolute; top: 10px; right: 10px; cursor: pointer;">
                    <svg width="24" height="24" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" stroke="#333" stroke-width="2"/></svg>
                </div>
                <h2>Approve Leave Request</h2>
                <div>
                    <input type="hidden" name="leave_id" readonly />
                    <label>Employee</label><input type="text" name="employee" readonly />
                    <label>Start Date</label><input type="text" name="lq_start_date" readonly />
                    <label>End Date</label><input type="text" name="lq_end_date" readonly />
                    <label>Duration</label><input type="text" name="duration" readonly />
                    <label>Reason</label><textarea name="lr_reason" readonly></textarea>
                    <label>Status</label><input type="text" name="status" readonly />
                </div>
                <div>
                    <button type="submit" name="approval" value="approve">Approve</button>
                    <button type="submit" name="approval" value="reject" style="background-color: red;">Reject</button>
                </div>
            </form>
        </dialog>
    </div>

    <script>
        const modal = document.getElementById('myModal');

        function openModal(id) {
            const row = document.querySelector(`#row-${id}`);
            if (!row) return;
            const data = JSON.parse(row.dataset.user);
            document.querySelector('input[name="leave_id"]').value = data.leave_id;
            document.querySelector('input[name="employee"]').value = data.first_name + ' ' + data.last_name;
            document.querySelector('input[name="lq_start_date"]').value = formatDate(data.start_date);
            document.querySelector('input[name="lq_end_date"]').value = formatDate(data.end_date);
            document.querySelector('textarea[name="lr_reason"]').value = data.reason;
            document.querySelector('input[name="status"]').value = data.status;

            const start = new Date(data.start_date);
            const end = new Date(data.end_date);
            const duration = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;
            document.querySelector('input[name="duration"]').value = duration + " day" + (duration > 1 ? "s" : "");

            modal.showModal();
        }

        function closeModal() {
            modal.close();
        }

        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
        }
    </script>
</body>
</html>
