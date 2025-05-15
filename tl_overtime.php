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
            // alert('Overtime request has been {$approvalStatus}.');
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
                                    <input type="text" id="employee_search" name="search"
                                        placeholder="Search overtime requests"
                                        value="<?php echo htmlspecialchars($searchQuery); ?>">
                                    <button type="submit" style="margin: 0px">
                                        <img class="img-resize" height="32px" width="32px"
                                            src="assets/images/icons/search-icon.png" alt="Search">
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
                                        <th>Employee</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Reason</th>
                                        <th>TL Approval</th>
                                        <th>TL Approval Date</th>
                                        <th>HR Manager Approval</th>
                                        <th>HR Manager Approval Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody class="font-medium">
                                    <?php if (!empty($overtimeRequests)): ?>
                                        <?php foreach ($overtimeRequests as $overtimeRequest): ?>
                                            <tr id="row-<?php echo $overtimeRequest['overtime_id'] ?>"
                                                data-user='<?php echo json_encode($overtimeRequest); ?>'
                                                class="employee-display-list text-center"
                                                onclick="openModal(<?php echo $overtimeRequest['overtime_id'] ?>)">
                                                <td style="text-align: center;">
                                                    <?php echo htmlspecialchars($overtimeRequest['first_name'] . " " . $overtimeRequest['last_name']) ?>
                                                </td>
                                                <td style="text-align: center;">
                                                    <?php echo formatDate($overtimeRequest['start_time']) ?>
                                                </td>
                                                <td style="text-align: center;">
                                                    <?php echo formatDate($overtimeRequest['end_time']) ?>
                                                </td>

                                                <td style="text-align: center;">
                                                    <?php echo htmlspecialchars($overtimeRequest['ot_reason']) ?>
                                                </td>

                                                <td style="text-align: center;"><?php
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
                                                ?></td>
                                                <td style="text-align: center;">
                                                    <?php echo formatDate($overtimeRequest['tl_approval_date']) ?>
                                                </td>
                                                <td style="text-align: center;"><?php
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
                                                ?></td>
                                                <td style="text-align: center;">
                                                    <?php echo formatDate($overtimeRequest['hr_manager_approval_date']) ?>
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
                    <div id="modal_close_btn" style="position: absolute; top: 10px; right: 10px; cursor: pointer;"
                        onclick="closeModal()">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 6L6 18" stroke="#333" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M6 6L18 18" stroke="#333" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-capitalize font-bold" style="font-size: 32px;margin: 0">Approve Overtime Request
                        </p>
                    </div>
                </div>
                <div id="modal-content" class="employee-detail-container">
                    <div class="flex flex-column gap-20 font-medium">
                        <div class="flex flex-row gap-20">
                            <div style="display: none" class="employee-detail-fields">
                                <label for="overtime_id">Overtime ID</label>
                                <input type="text" name="overtime_id" readonly>
                            </div>

                            <div class="employee-detail-fields">
                                <label for="employee">Employee</label>
                                <input id="employee" type="text" name="employee" readonly>
                            </div>

                            <div class="employee-detail-fields">
                                <label for="start_time">Overtime Date</label>
                                <input id="start_time" type="text" name="start_time" readonly>
                            </div>

                            <div class="employee-detail-fields">
                                <label for="end_time">End Time</label>
                                <input id="end_time" type="text" name="end_time" readonly>
                            </div>

                        </div>

                        <div class="flex flex-row gap-20 space-around">

                            <div class="employee-detail-fields">
                                <label for="duration">Duration (hours)</label>
                                <input id="duration" type="text" name="duration" readonly>
                            </div>

                            <div class="employee-detail-fields">
                                <label for="remarks">Reason</label>
                                <textarea rows="5" name="remarks" readonly></textarea>
                            </div>

                            <div class="employee-detail-fields">
                                <label for="status">Status</label>
                                <input type="text" name="status" readonly>
                            </div>
                        </div>

                        <div class="flex flex-row gap-20 space-around">
                            <div class="flex flex-column">
                                <div class="employee-detail-fields">
                                    <label for="tl_approval">Team Leader Approval</label>
                                    <input type="text" name="tl_approval" readonly>
                                </div>
                                <div class="employee-detail-fields">
                                    <label for="tl_approval_date">Approval Date</label>
                                    <input type="text" name="tl_approval_date" readonly>
                                </div>
                            </div>

                            <div class="flex flex-column">
                                <div class="employee-detail-fields">
                                    <label for="hr_manager_approval">HR Manager Approval</label>
                                    <input type="text" name="hr_manager_approval" readonly>
                                </div>
                                <div class="employee-detail-fields">
                                    <label for="hr_manager_approval_date">Approval Date</label>
                                    <input type="text" name="hr_manager_approval_date" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="employee-detail-edit-button-container">
                    <div>
                        <button class="employee-detail-edit-button" type="submit" value="approve"
                            name="approval">Approve</button>
                    </div>
                    <div>
                        <button class="employee-detail-edit-button cancel-button" type="submit" value="reject"
                            name="approval">Reject</button>
                    </div>
                </div>
            </form>
        </dialog>
    </div>

    <script>
        function calculateHours(startDateTime, endDateTime) {
            let start = new Date(startDateTime);
            let end = new Date(endDateTime);

            let differenceInMs = end - start;
            let differenceInHours = differenceInMs / (1000 * 60 * 60);
            return differenceInHours;
        }
        const modal = document.getElementById('myModal');

        function openModal(id) {
            const currentRow = document.querySelector(`#row-${id}`);


            let data;

            try {
                data = JSON.parse(currentRow.dataset.user);
            } catch (error) {
                console.error("Error parsing JSON data from row dataset:", error);
                return;
            }
            console.log(data)


            Object.keys(data).forEach(key => {
                const inputElement = modal.querySelector(`[name="${key}"]`);
                if (inputElement) {
                    inputElement.value = data[key];
                }
            });

            const employeeName = document.querySelector(`input[name="employee"`);
            if (employeeName) {
                employeeName.value = data.first_name + " " + data.last_name;
            }

            const ot_reason = document.querySelector(`textarea[name="remarks"]`);
            if (ot_reason) {
                ot_reason.value = data.ot_reason;
            }

            const ot_duration = document.querySelector(`input[name="duration"]`);
            if (ot_duration) {
                ot_duration.value = calculateHours(data.start_time, data.end_time);
            }

            modal.showModal();
        }

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });

        function closeModal() {
            modal.close();
        }
    </script>
</body>

</html>