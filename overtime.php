<?php
session_start();

if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

$user_account_id = (int) $_SESSION["user_account_id"];

include 'includes/database.php';
include 'config.php';

function formatDate($rawDate, $format = 'M d, Y') {
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approval'])) {
    $sql = "UPDATE leave_request SET tl_approval = :tl_approval WHERE leave_id = :leave_id";
    $stmt_update = $pdo->prepare($sql);
    $stmt_update->execute([
        ':tl_approval' => $_POST['approval'] === 'approve' ? 'Approved' : 'Rejected',
        ':leave_id' => $_POST['leave_id']
    ]);
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
                       AND (
                           e.first_name LIKE :searchQuery 
                           OR e.middle_name LIKE :searchQuery 
                           OR e.last_name LIKE :searchQuery 
                           OR lr.leave_type LIKE :searchQuery 
                           OR lr.status LIKE :searchQuery
                       )
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
    <meta charset="UTF-8">
    <title>Team Leader | Leave Requests</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
    <div id="admin">
        <div class="dashboard-background">
            <div class="dashboard-container">
                <div class="dashboard-navigation">
                    <?php include('includes/navigation.php') ?>
                </div>
                <div class="dashboard-content">
                    <div class="dashboard-content-header">
                        <h1>LEAVE REQUESTS</h1>
                        <?php include('includes/header-avatar.php') ?>
                    </div>

                    <form method="GET" action="tl_leave_requests.php">
                        <input type="text" id="employee_search" name="search" placeholder="Search leave requests" value="<?php echo htmlspecialchars($searchQuery); ?>">
                        <button type="submit"><img src="assets/images/icons/search-icon.png" alt="Search" width="32" height="32"></button>
                        <?php if (!empty($searchQuery)): ?>
                            <a href="tl_leave_requests.php">Clear Search</a>
                        <?php endif; ?>
                    </form>

                    <div class="employee-display" style="overflow:auto;">
                        <table class="employee-table">
                            <thead>
                                <tr>
                                    <th>Employee</th><th>Type</th><th>Start</th><th>End</th><th>Duration</th>
                                    <th>Reason</th><th>TL Approval</th><th>TL Date</th><th>HR Approval</th><th>HR Date</th><th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($leaveRequests)): ?>
                                    <?php foreach ($leaveRequests as $leaveRequest): ?>
                                        <tr id="row-<?php echo $leaveRequest['employee_id']; ?>" onclick="openModal(<?php echo $leaveRequest['employee_id']; ?>)" data-user='<?php echo json_encode($leaveRequest); ?>'>
                                            <td><?php echo $leaveRequest['first_name'] . ' ' . $leaveRequest['last_name']; ?></td>
                                            <td><?php echo htmlspecialchars($leaveRequest['leave_type']); ?></td>
                                            <td><?php echo formatDate($leaveRequest['start_date']); ?></td>
                                            <td><?php echo formatDate($leaveRequest['end_date']); ?></td>
                                            <td>
                                                <?php
                                                $start = new DateTime($leaveRequest['start_date']);
                                                $end = new DateTime($leaveRequest['end_date']);
                                                echo $start && $end ? $start->diff($end)->days + 1 . ' days' : 'N/A';
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($leaveRequest['reason']); ?></td>
                                            <td><?php echo $leaveRequest['tl_approval']; ?></td>
                                            <td><?php echo formatDate($leaveRequest['tl_approval_date']); ?></td>
                                            <td><?php echo $leaveRequest['hr_manager_approval']; ?></td>
                                            <td><?php echo formatDate($leaveRequest['hr_manager_approval_date']); ?></td>
                                            <td><?php echo $leaveRequest['status']; ?></td>
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

        <!-- Modal Dialog -->
        <dialog id="myModal">
            <form method="POST" class="modal-form">
                <button type="button" onclick="closeModal()">X</button>
                <input type="hidden" name="leave_id">

                <p>Employee: <input name="employee" readonly></p>
                <p>Start Date: <input name="lq_start_date" readonly></p>
                <p>End Date: <input name="lq_end_date" readonly></p>
                <p>Duration: <input name="duration" readonly></p>
                <p>Reason: <textarea name="lr_reason" readonly></textarea></p>
                <p>Status: <input name="status" readonly></p>
                <p>TL Approval: <input name="tl_approval" readonly></p>
                <p>TL Date: <input name="tl_approval_date" readonly></p>
                <p>HR Approval: <input name="hr_manager_approval" readonly></p>
                <p>HR Date: <input name="hr_manager_approval_date" readonly></p>

                <button type="submit" name="approval" value="approve">Approve</button>
                <button type="submit" name="approval" value="reject">Reject</button>
            </form>
        </dialog>
    </div>

<script>
    const modal = document.getElementById('myModal');

    function openModal(id) {
        const row = document.getElementById(`row-${id}`);
        const data = JSON.parse(row.dataset.user);

        document.querySelector('input[name="leave_id"]').value = data.leave_id;
        document.querySelector('input[name="employee"]').value = data.first_name + ' ' + data.last_name;
        document.querySelector('input[name="lq_start_date"]').value = formatDate(data.start_date);
        document.querySelector('input[name="lq_end_date"]').value = formatDate(data.end_date);
        document.querySelector('input[name="duration"]').value = calcDuration(data.start_date, data.end_date);
        document.querySelector('textarea[name="lr_reason"]').value = data.reason;
        document.querySelector('input[name="status"]').value = data.status;
        document.querySelector('input[name="tl_approval"]').value = data.tl_approval;
        document.querySelector('input[name="tl_approval_date"]').value = formatDate(data.tl_approval_date);
        document.querySelector('input[name="hr_manager_approval"]').value = data.hr_manager_approval;
        document.querySelector('input[name="hr_manager_approval_date"]').value = formatDate(data.hr_manager_approval_date);

        modal.showModal();
    }

    function closeModal() {
        modal.close();
    }

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
