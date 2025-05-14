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

$overtimeRequests = [];
$searchQuery = "";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approval'])) {
        $overtime_id = $_POST['overtime_id'];

        $sql = "UPDATE overtime SET tl_approval = :tl_approval WHERE overtime_id = :overtime_id";
        $stmt_update = $pdo->prepare($sql);

        $approvalStatus = $_POST['approval'] === 'approve' ? 'Approved' : 'Rejected';
        $stmt_update->execute([
            ':tl_approval' => $approvalStatus,
            ':overtime_id' => $overtime_id,
        ]);

        echo "<script>
            alert('Overtime request has been {$approvalStatus}.');
            window.location.href = 'tl_overtime.php';
        </script>";
        exit();
    }

    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $searchQuery = trim($_GET['search']);
        $sql_search = "SELECT ot.*, e.first_name, e.last_name
                       FROM overtime ot
                       INNER JOIN employee e ON ot.employee_id = e.employee_id
                       WHERE e.team_leader_id = :loggedInTeamLeaderId
                       AND (e.first_name LIKE :searchQuery 
                       OR e.last_name LIKE :searchQuery 
                       OR ot.status LIKE :searchQuery)
                       ORDER BY ot.date_created DESC";

        $stmt_search = $pdo->prepare($sql_search);
        $stmt_search->bindValue(':loggedInTeamLeaderId', $_SESSION['employee_id'], PDO::PARAM_INT);
        $stmt_search->bindValue(':searchQuery', "%$searchQuery%", PDO::PARAM_STR);
        $stmt_search->execute();
        $overtimeRequests = $stmt_search->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $sql_overtime = "SELECT ot.*, e.first_name, e.last_name
                         FROM overtime ot
                         INNER JOIN employee e ON ot.employee_id = e.employee_id
                         WHERE e.team_leader_id = :loggedInTeamLeaderId
                         ORDER BY ot.date_created DESC";
        $stmt_overtime = $pdo->prepare($sql_overtime);
        $stmt_overtime->bindValue(':loggedInTeamLeaderId', $_SESSION['employee_id'], PDO::PARAM_INT);
        $stmt_overtime->execute();
        $overtimeRequests = $stmt_overtime->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
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
                        <div class="employee-header">
                            <div class="employee-header-div">
                                <form method="GET" action="tl_overtime.php">
                                    <input type="text" id="employee_search" name="search" placeholder="Search overtime requests"
                                        value="<?php echo htmlspecialchars($searchQuery); ?>">
                                    <button type="submit" style="margin: 0px">
                                        <img class="img-resize" height="32px" width="32px" src="assets/images/icons/search-icon.png" alt="Search">
                                    </button>
                                    <div>
                                        <?php if (!empty($searchQuery)): ?>
                                            <a href="tl_overtime.php" class="clear-search">Clear Search</a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="employee-display" style="overflow: auto;">
                            <table class="employee-table">
                                <thead class="font-bold">
                                    <tr>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>OT Type</th>
                                        <th>Remarks</th>
                                        <th>Status</th>
                                        <th>TL Approval</th>
                                        <th>Approval Date</th>
                                    </tr>
                                </thead>
                                <tbody class="font-medium">
                                    <?php if (!empty($overtimeRequests)): ?>
                                        <?php foreach ($overtimeRequests as $overtimeRequest): ?>
                                            <tr id="row-<?php echo $overtimeRequest['overtime_id'] ?>" class="employee-display-list text-center" onclick="openModal(<?php echo $overtimeRequest['overtime_id'] ?>)">
                                                <td><?php echo formatDate($overtimeRequest['start_time']) ?></td>
                                                <td><?php echo formatDate($overtimeRequest['end_time']) ?></td>
                                                <td><?php echo htmlspecialchars($overtimeRequest['ot_type']) ?></td>
                                                <td><?php echo htmlspecialchars($overtimeRequest['remarks']) ?></td>
                                                <td><?php echo htmlspecialchars($overtimeRequest['status']) ?></td>
                                                <td>
                                                    <form action="tl_overtime.php" method="POST">
                                                        <input type="hidden" name="overtime_id" value="<?php echo $overtimeRequest['overtime_id']; ?>">
                                                        <button type="submit" name="approval" value="approve">Approve</button>
                                                        <button type="submit" name="approval" value="reject">Reject</button>
                                                    </form>
                                                </td>
                                                <td><?php echo formatDate($overtimeRequest['tl_approval_date']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7">No overtime requests found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <dialog style="border-radius: 10px; border: 1px solid #D1D1D1;" id="myModal">
            <form action="tl_overtime.php" method="POST" class="flex flex-column gap-20">
                <div>
                    <div id="modal_close_btn" style="position: absolute; top: 10px; right: 10px; cursor: pointer;" onclick="closeModal()">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 6L6 18" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M6 6L18 18" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-capitalize font-bold" style="font-size: 32px;margin: 0">Approve Overtime Request</p>
                    </div>
                </div>
                <div id="modal-content" class="employee-detail-container">
                    <div class="employee-detail-container-group gap-20 font-medium">
                        <div class="flex flex-row gap-20">
                            <div class="employee-detail-fields">
                                <label for="start_time">Overtime Start Time</label>
                                <input id="start_time" type="text" name="start_time" readonly>
                            </div>

                            <div class="employee-detail-fields">
                                <label for="end_time">Overtime End Time</label>
                                <input id="end_time" type="text" name="end_time" readonly>
                            </div>

                            <div class="employee-detail-fields">
                                <label for="ot_type">OT Type</label>
                                <input id="ot_type" type="text" name="ot_type" readonly>
                            </div>

                            <div class="employee-detail-fields">
                                <label for="remarks">Reason</label>
                                <textarea rows="5" name="remarks" readonly></textarea>
                            </div>
                        </div>

                        <div class="flex flex-row gap-20 space-around">
                            <div class="flex flex-column">
                                <div class="employee-detail-fields">
                                    <label for="tl_approval">Team Leader Approval</label>
                                    <input type="text" name="tl_approval">
                                </div>
                                <div class="employee-detail-fields">
                                    <label for="tl_approval_date">Approval Date</label>
                                    <input type="text" name="tl_approval_date">
                                </div>
                            </div>

                            <div class="flex flex-column">
                                <div class="employee-detail-fields">
                                    <label for="status">Status</label>
                                    <input type="text" name="status" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="employee-detail-edit-button-container">
                    <div>
                        <button class="employee-detail-edit-button" type="submit" value="approve" name="approval">Approve</button>
                    </div>
                    <div>
                        <button class="employee-detail-edit-button cancel-button" type="submit" value="reject" name="approval">Reject</button>
                    </div>
                </div>
            </form>
        </dialog>
    </div>

    <script>
        const modal = document.getElementById('myModal');

        function openModal(id) {
            const currentRow = document.querySelector(`#row-${id}`);
            let data = {
                'start_time': currentRow.cells[0].innerText,
                'end_time': currentRow.cells[1].innerText,
                'ot_type': currentRow.cells[2].innerText,
                'remarks': currentRow.cells[3].innerText,
                'status': currentRow.cells[4].innerText,
                'tl_approval': currentRow.cells[5].innerText,
            };

            Object.keys(data).forEach(key => {
                const inputElement = modal.querySelector(`[name="${key}"]`);
                if (inputElement) {
                    inputElement.value = data[key];
                }
            });

            modal.showModal();
        }

        function closeModal() {
            modal.close();
        }
    </script>
</body>
</html>
