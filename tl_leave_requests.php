<?php
session_start();

if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

// if (!$_SESSION['isTeamLeader']) {
//     header("Location: index.php");
//     exit();
// }

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
                        e.last_name,
                        e.job_id,
                        j.job_name,
                        j.department_id,
                        d.department_name
                   FROM
                        leave_request lr
                   INNER JOIN
                        employee e ON lr.employee_id = e.employee_id
                   INNER JOIN
                        job j ON e.job_id = j.job_id
                   INNER JOIN
                        department d ON j.department_id = d.department_id
                   WHERE
                        (lr.hr_manager_approval = 'Pending' AND lr.tl_approval= 'Approved') OR
                        lr.status = 'Approved'
                   ORDER BY
                        lr.date_created DESC";

    $stmt_leaves = $pdo->prepare($sql_leaves);
    $stmt_leaves->execute();
    $leaveRequests = $stmt_leaves->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching leave request data: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approval'])) {
        print_r($_POST);
        $sql = "UPDATE leave_request SET tl_approval = :tl_approval WHERE leave_id = :leave_id";

        $stmt_update = $pdo->prepare($sql);
        if ($_POST['approval'] == 'approve') {
            echo 1;
            $stmt_update->execute([
                ':hr_manager_approval' => 'Approved',
                ':leave_id' => $_POST['leave_id'],
            ]);
        } else {
            echo 2;
            $stmt_update->execute([
                ':hr_manager_approval' => 'Rejected',
                ':leave_id' => $_POST['leave_id'],
            ]);
        }

    }

    header("Location: hr_manager_leave_requests.php");
    exit();
}


$searchQuery = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchQuery = trim($_GET['search']);

    try {
        $sql_search = "SELECT
                            lr.*,
                            e.first_name,
                            e.middle_name,
                            e.last_name,
                            e.job_id,
                            j.job_name,
                            j.department_id,
                            d.department_name
                        FROM leave_request lr
                        INNER JOIN employee e ON lr.employee_id = e.employee_id
                        INNER JOIN job j ON e.job_id = j.job_id
                        INNER JOIN department d ON j.department_id = d.department_id
                        WHERE ((lr.hr_manager_approval = 'Pending' AND lr.tl_approval = 'Approved')
                            OR lr.status = 'Approved')
                        AND (
                            e.first_name LIKE :searchQuery OR
                            e.middle_name LIKE :searchQuery OR
                            e.last_name LIKE :searchQuery OR
                            lr.leave_type LIKE :searchQuery OR
                            lr.status LIKE :searchQuery OR
                            j.job_name LIKE :searchQuery OR
                            d.department_name LIKE :searchQuery
                        )
                        ORDER BY lr.date_created DESC
                        ";

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
                            <h1>LEAVE REQUESTS - <span style="font-size: 12px;"> TEAM LEADER APPROVAL</span></h1>
                        </div>
                        <div id="logout-admin" class="dashboard-content-header font-medium">
                            <?php include('includes/header-avatar.php') ?>
                        </div>
                    </div>

                    <div class="employee-main-content">
                        <div class="employee-header">
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

                        </div>

                        <div class="employee-display" style="overflow: auto;">
                            <table class="employee-table">
                                <thead class="font-bold">
                                    <tr>
                                        <th>Employee</th>
                                        <th>Job</th>
                                        <th>Department</th>
                                        <th>Type</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>No. of Days</th>
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
                                            <tr id="row-<?php echo $leaveRequest['leave_id'] ?>" style="white-space: nowrap;"
                                                data-user='<?php echo json_encode($leaveRequest); ?>'
                                                class="employee-display-list text-center"
                                                onclick="openModal(<?php echo $leaveRequest['leave_id'] ?>)">
                                                <td style="text-align: center;">
                                                    <?php echo $leaveRequest['first_name'] . " " . $leaveRequest['last_name'] ?>
                                                </td>
                                                <td style="text-align: center;">
                                                    <?php echo $leaveRequest['job_name'] ?>
                                                </td>
                                                <td style="text-align: center;">
                                                    <?php echo $leaveRequest['department_name'] ?>
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
                                            <td colspan="9">No leave requests found.</td>
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

            <form action="tl_leave_requests.php" method="POST" class="flex flex-column gap-20">

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
                        <p class="text-capitalize font-bold" style="font-size: 32px;margin: 0">Approve Leave Request</p>
                    </div>
                </div>
                <div id="modal-content" class="employee-detail-container">
                    <div class="employee-detail-container-group gap-20 font-medium">
                        <div class="flex flex-column gap-20">
                            <div class="flex flex-row space-between">
                                <div style="display: none" class="employee-detail-fields">
                                    <label for="leave_id">Leave ID</label>
                                    <input type="text" name="leave_id" readonly>
                                </div>

                                <div class="employee-detail-fields">
                                    <label for="employee">Employee</label>
                                    <input id="employee" type="text" name="employee" readonly>
                                </div>

                                <div class="employee-detail-fields">
                                    <label for="job_name">Job</label>
                                    <input id="job_name" type="text" name="job_name" readonly>
                                </div>

                                <div class="employee-detail-fields">
                                    <label for="department_name">Department</label>
                                    <input id="department_name" type="text" name="department_name" readonly>
                                </div>

                            </div>

                            <div class="flex flex-row gap-20 space-between">

                                <div class="employee-detail-fields flex flex-column">
                                    <label for="lq_start_date">Start Date</label>
                                    <input id="lq_start_date" type="text" name="lq_start_date" readonly>
                                </div>

                                <div class="employee-detail-fields flex flex-column">
                                    <label for="lq_end_date">End Date</label>
                                    <input id="lq_end_date" type="text" name="lq_end_date" readonly>
                                </div>

                                <div class="employee-detail-fields">
                                    <label for="duration">No. of Days</label>
                                    <input type="text" name="duration" readonly>
                                </div>



                            </div>

                            <div class="flex flex-row gap-20">
                                <div class="employee-detail-fields">
                                    <label for="lr_reason">Reason</label>
                                    <textarea wrap="hard" rows="5" cols="65"
                                        style="width: 100%;padding: 7px 5px;border-radius: 8px;border: 1px solid var(--color-base-300); font-size: 16px;word-wrap: break-word;"
                                        type="text" name="lr_reason" readonly></textarea>
                                </div>
                            </div>

                            <div class="flex flex-row gap-20 space-around">
                                <div class="flex flex-column">
                                    <div class="employee-detail-fields">
                                        <label for="tl_approval">Team Leader Approval</label>
                                        <input type="text" name="tl_approval" readonly>

                                        </input>
                                    </div>

                                    <div class="employee-detail-fields">
                                        <label for="tl_approval_date">Approval Date</label>
                                        <input type="text" name="tl_approval_date" readonly>
                                    </div>
                                    <div class="vl"></div>
                                </div>

                                <div class="flex flex-column">
                                    <div class="employee-detail-fields">
                                        <label for="hr_manager_approval">HR Manager Approval</label>
                                        <input type="text" name="hr_manager_approval" readonly>

                                        </input>
                                    </div>

                                    <div class="employee-detail-fields">
                                        <label for="hr_manager_approval_date">Approval Date</label>
                                        <input type="text" name="hr_manager_approval_date" readonly>
                                    </div>
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
                        <button style="background-color: var(--color-error)"
                            class="employee-detail-edit-button cancel-button" type="submit" value="reject"
                            name="approval">Reject</button>
                    </div>

                </div>
            </form>
        </dialog>

    </div>


</body>

<script>
    const modal = document.getElementById('myModal');
    const addEmployeeModal = document.getElementById('add_employee_modal');


    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });

    function formatDate(dateString) {
        if (!dateString) return "N/A";

        const date = new Date(dateString);

        if (isNaN(date.getTime())) return "Invalid Date";

        return date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    }

    console.log(formatDate("2025-03-29"));




    function openModal(id) {
        const currentRow = document.querySelector(`#row-${id}`);
        console.log(currentRow)
        if (!currentRow) {
            console.error(`Row with id row-${id} not found`);
            return;
        }

        let data;
        try {
            data = JSON.parse(currentRow.dataset.user);
        } catch (error) {
            console.error("Error parsing JSON data from row dataset:", error);
            return;
        }

        Object.keys(data).forEach(key => {
            const inputElement = document.querySelector(`input[name="${key}"]`);
            if (inputElement) {
                inputElement.value = data[key];

            }
        });

        const employeeName = document.querySelector(`input[name="employee"`);
        if (employeeName) {
            employeeName.value = data.first_name + " " + data.last_name;
        }


        const reasonTextArea = document.querySelector('textarea[name="lr_reason"]');
        if (reasonTextArea && data.reason) {
            reasonTextArea.value = data.reason;
        }

        const lqStartDate = document.querySelector(`input[name="lq_start_date"`);
        if (lqStartDate && data.start_date) {
            lqStartDate.value = formatDate(data.start_date);
        }

        const lqEndDate = document.querySelector(`input[name="lq_end_date"`);
        if (lqEndDate && data.end_date) {
            lqEndDate.value = formatDate(data.end_date);
        }

        const lqDuration = document.querySelector(`input[name="duration"`);
        if (lqDuration) {
            const start = new Date(data.start_date);
            const end = new Date(data.end_date);
            const differenceInMilliseconds = end.getTime() - start.getTime();
            const durationInDays = Math.floor(differenceInMilliseconds / (1000 * 60 * 60 * 24));
            lqDuration.value = durationInDays + 1;
        }

        const statusSelect = document.querySelector(`select[name="status"]`);
        if (statusSelect && data.status) {
            statusSelect.value = data.status;
        }

        document.querySelector('textarea[name="reason"]').value = "Test value";

        modal.showModal();
    }

    function closeModal() {
        modal.close();
    }

    document.getElementById('employee_search').addEventListener('input', function () {
        document.getElementById('employee_search').submit();
    });

    function searchEmployees() {
        const input = document.getElementById('employeeSearch');
        const filter = input.value.toUpperCase();
        const table = document.querySelector('.employee-display table');
        const rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            let found = false;
            const cells = rows[i].getElementsByTagName('td');

            for (let j = 0; j < cells.length; j++) {
                const cell = cells[j];
                if (cell) {
                    const textValue = cell.textContent || cell.innerText;
                    if (textValue.toUpperCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }

            if (found) {
                rows[i].style.display = '';
            } else {
                rows[i].style.display = 'none';
            }
        }
    }

</script>


</html>