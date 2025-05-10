<?php
session_start();

if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

$user_account_id = (int) $_SESSION["user_account_id"];

include 'includes/database.php';
include 'config.php';

function formatDate($rawDate, $format = 'M d, Y h:i A') {
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
    $sql_ot = "SELECT
                    ot.*,
                    e.first_name,
                    e.middle_name,
                    e.last_name
               FROM
                    overtime ot
               INNER JOIN
                    employee e ON ot.employee_id = e.employee_id
               WHERE
                    e.team_leader_id = :loggedInTeamLeaderId
               ORDER BY
                    ot.date_created DESC";

    $stmt_ot = $pdo->prepare($sql_ot);
    $stmt_ot->bindValue(':loggedInTeamLeaderId', $_SESSION['employee_id'], PDO::PARAM_INT);
    $stmt_ot->execute();
    $overtimeRequests = $stmt_ot->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching overtime data: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approval'])) {
        $sql = "UPDATE overtime SET tl_approval = :tl_approval, tl_approval_date = NOW() WHERE overtime_id = :overtime_id";
        $stmt_update = $pdo->prepare($sql);
        $status = $_POST['approval'] === 'approve' ? 'Approved' : 'Rejected';
        $stmt_update->execute([
            ':tl_approval' => $status,
            ':overtime_id' => $_POST['overtime_id'],
        ]);
    }

    header("Location: tl_overtime_requests.php");
    exit();
}
?>

<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Leader | Overtime Requests</title>
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
                        <div class="employee-display" style="overflow: auto;">
                            <table class="employee-table">
                                <thead class="font-bold">
                                    <tr>
                                        <th>Employee</th>
                                        <th>Type</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Remarks</th>
                                        <th>TL Approval</th>
                                        <th>Approval Date</th>
                                        <th>HR Approval</th>
                                        <th>Approval Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody class="font-medium">
                                    <?php if (!empty($overtimeRequests)): ?>
                                        <?php foreach ($overtimeRequests as $ot): ?>
                                            <tr id="row-<?php echo $ot['overtime_id'] ?>"
                                                data-user='<?php echo json_encode($ot); ?>'
                                                class="employee-display-list text-center"
                                                onclick="openModal(<?php echo $ot['overtime_id'] ?>)">
                                                <td><?php echo $ot['first_name'] . " " . $ot['last_name'] ?></td>
                                                <td><?php echo htmlspecialchars($ot['ot_type']); ?></td>
                                                <td><?php echo formatDate($ot['start_time']); ?></td>
                                                <td><?php echo formatDate($ot['end_time']); ?></td>
                                                <td><?php echo htmlspecialchars($ot['remarks']); ?></td>
                                                <td><?php echo $ot['tl_approval']; ?></td>
                                                <td><?php echo formatDate($ot['tl_approval_date']); ?></td>
                                                <td><?php echo $ot['hr_manager_approval']; ?></td>
                                                <td><?php echo formatDate($ot['hr_manager_approval_date']); ?></td>
                                                <td><?php echo $ot['status']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="10">No overtime requests found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Dialog -->
        <dialog id="myModal">
            <form method="POST" class="modal-form">
                <input type="hidden" name="overtime_id">
                <div>
                    <label>Employee</label>
                    <input name="employee" readonly>
                </div>
                <div>
                    <label>Start Time</label>
                    <input name="start_time" readonly>
                </div>
                <div>
                    <label>End Time</label>
                    <input name="end_time" readonly>
                </div>
                <div>
                    <label>Remarks</label>
                    <textarea name="remarks" readonly></textarea>
                </div>
                <div class="modal-actions">
                    <button type="submit" name="approval" value="approve">Approve</button>
                    <button type="submit" name="approval" value="reject">Reject</button>
                    <button type="button" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </dialog>
    </div>
</body>

<script>
    const modal = document.getElementById('myModal');

    function openModal(id) {
        const row = document.getElementById('row-' + id);
        const data = JSON.parse(row.dataset.user);

        document.querySelector('input[name="overtime_id"]').value = data.overtime_id;
        document.querySelector('input[name="employee"]').value = data.first_name + " " + data.last_name;
        document.querySelector('input[name="start_time"]').value = new Date(data.start_time).toLocaleString();
        document.querySelector('input[name="end_time"]').value = new Date(data.end_time).toLocaleString();
        document.querySelector('textarea[name="remarks"]').value = data.remarks;

        modal.showModal();
    }

    function closeModal() {
        modal.close();
    }
</script>
</html>
