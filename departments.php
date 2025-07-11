<?php
session_start();

if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

include 'includes/database.php';
include 'config.php';

$searchTerm = '';
if (isset($_GET['search'])) {
    $searchTerm = trim($_GET['search']);
}

try {
    if (!empty($searchTerm)) {
        $sql = "SELECT
                    d.department_id,
                    d.department_name,
                    d.branch,
                    d.status AS department_status,
                    tl.employee_id AS team_leader_id,
                    tl.first_name AS team_leader_first_name,
                    tl.last_name AS team_leader_last_name,
                    m.employee_id AS manager_id,
                    m.first_name AS manager_first_name,
                    m.last_name AS manager_last_name,
                    COUNT(CASE WHEN j.status = 'Active' THEN j.job_id END) AS active_job_count,
                    COUNT(CASE WHEN j.status = 'Active' AND e.status = 'Active' THEN e.employee_id END) AS active_employee_count
                FROM department d
                LEFT JOIN employee tl ON d.team_leader_id = tl.employee_id
                LEFT JOIN employee m ON d.manager_id = m.employee_id
                LEFT JOIN job j ON d.department_id = j.department_id
                LEFT JOIN employee e ON j.job_id = e.job_id
                WHERE
                    d.department_name LIKE :search
                    OR d.branch LIKE :search
                    OR d.status LIKE :search
                    OR tl.first_name LIKE :search
                    OR tl.last_name LIKE :search
                    OR m.first_name LIKE :search
                    OR m.last_name LIKE :search
                    OR j.job_name LIKE :search
                    OR j.description LIKE :search
                    OR CAST(j.salary AS CHAR) LIKE :search
                    OR j.salary_frequency LIKE :search
                    OR j.status LIKE :search
                    OR e.first_name LIKE :search
                    OR e.last_name LIKE :search
                    OR e.status LIKE :search
                GROUP BY d.department_id, d.department_name, d.branch, d.status, tl.employee_id, tl.first_name, tl.last_name, m.employee_id, m.first_name, m.last_name
                ORDER BY d.department_name";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':search', "%" . $searchTerm . "%", PDO::PARAM_STR);
    } else {
        $sql = "SELECT
                    d.department_id,
                    d.department_name,
                    d.branch,
                    d.status AS department_status,
                    tl.employee_id AS team_leader_id,
                    tl.first_name AS team_leader_first_name,
                    tl.last_name AS team_leader_last_name,
                    m.employee_id AS manager_id,
                    m.first_name AS manager_first_name,
                    m.last_name AS manager_last_name,
                    (SELECT COUNT(*) 
                    FROM job j 
                    WHERE j.department_id = d.department_id 
                    AND j.status = 'Active') AS active_job_count,
                    (SELECT COUNT(*) 
                    FROM employee e
                    JOIN job j2 ON e.job_id = j2.job_id
                    WHERE j2.department_id = d.department_id
                    AND e.status = 'Active') AS active_employee_count
                FROM department d
                LEFT JOIN employee tl ON d.team_leader_id = tl.employee_id
                LEFT JOIN employee m ON d.manager_id = m.employee_id
                ORDER BY d.department_name;
                ";
        $stmt = $pdo->prepare($sql);
    }

    $stmt->execute();
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error loading data: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['edit-department-detail'])) {
        print_r($_POST);
        // $sql = 'SELECT * FROM department WHERE department_id = :department_id';
        // $stmt = $pdo->prepare($sql);
        // $stmt->execute([
        //     ':department_id' => $_POST['department_id']
        // ]);
        // $dept = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // print_r($dept);

        // $lastManager = '';
        // $lastTL = '';

        // foreach ($dept as $d) {
        //     $lastManager = $d['team_leader_id'];
        //     $lastTL = $d['manager_id'];
        // }

        $sql = 'UPDATE department
        SET department_name = :department_name,
            branch = :branch,
            manager_id = :manager_id,
            team_leader_id = :team_leader_id,
            status = :status
        WHERE department_id = :department_id';
        $stmt = $pdo->prepare($sql);
        // $stmt->execute([
        //     ':department_name' => $_POST['department_name'],
        //     ':branch' => $_POST['branch'],
        //     ':manager_id' => $_POST['manager_id'],
        //     ':team_leader_id' => $_POST['team_leader_id'],
        //     ':status' => $_POST['status'],
        //     ':department_id' => $_POST['department_id']
        // ]);

        // $sql = 'UPDATE employee
        // SET team_leader_id = NULL,
        // WHERE employee_id = :employee_id
        // ';

    }

    if (isset($_POST['add-department'])) {
        $sql = 'INSERT INTO department 
        (department_name, manager_id, team_leader_id, branch, status) 
        VALUES (:department_name, :manager_id, :team_leader_id, :branch, :status)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':department_name' => $_POST['department_name'],
            ':manager_id' => $_POST['manager_id'],
            ':team_leader_id' => $_POST['team_leader_id'],
            ':branch' => $_POST['branch'],
            ':status' => $_POST['status']
        ]);
    }

    if (isset($_POST['delete'])) {
        $sql = 'DELETE FROM department WHERE department_id = :department_id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':department_id' => $_POST['department_id'],
        ]);
    }

    // header("Location: departments.php");

}


try {
    $sql = "SELECT * FROM employee";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("" . $e->getMessage());
}

?>

<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Admin'; ?> | Departments</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
</head>

<body class="font-medium">

    <div class="dashboard-background">
        <div class="dashboard-container">
            <div class="dashboard-navigation">
                <?php include('includes/navigation.php') ?>
            </div>
            <div class="dashboard-content">
                <div class="dashboard-content-item1">
                    <div class="dashboard-content-header font-black">
                        <h1>DEPARTMENTS</h1>
                    </div>
                    <div id="logout-admin" class="dashboard-content-header font-medium">
                        <?php include('includes/header-avatar.php') ?>
                    </div>
                </div>

                <div class="employee-main-content" style="padding: 0; border: 0;">
                    <div class="employee-display" style="background: var(--color-base-200); border: 0">
                        <div class="jobs-bottom-container">
                            <div id="Tab1" class="tabcontent" role="tabpanel">
                                <div class="job-display-container">
                                    <div class="flex flex-row space-between align-center">

                                        <form class="flex flex-row" method="GET" action="departments.php"
                                            style="margin:0">
                                            <input type="text" id="job_search" name="search"
                                                placeholder="Search Departments..."
                                                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">

                                            <div><img height="32px" width="32px"
                                                    src="assets/images/icons/search-icon.png" alt="Search">
                                            </div>

                                            <div>
                                                <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                                                    <a href="departments.php" class="clear-search">Clear Search</a>
                                                <?php endif; ?>
                                            </div>
                                        </form>

                                        <div class="font-bold font-size-20">
                                            <?php
                                            $deptCount = isset($departments) ? count($departments) : 0;
                                            echo $deptCount . " Department" . ($deptCount >= 1 ? "s" : "");
                                            ?>
                                        </div>

                                        <div><button id="add-job-btn" onclick="addJobModal()">Add Department</button>
                                        </div>

                                    </div>
                                    <div class="jobs-list-container">
                                        <div id="search-results" class="jobs-list">
                                            <?php
                                            foreach ($departments as $department) {
                                                ?>
                                                <div id="job-<?php echo json_encode($department['department_id']); ?>"
                                                    class="job-record"
                                                    onclick="showDetailDialog(<?php echo json_encode($department['department_id']) ?>)"
                                                    data-job='<?php echo json_encode($department); ?>'>
                                                    <div id="job-display-top" class="flex flex-row space-between gap-10">
                                                        <div class="flex flex-row align-center gap-10">
                                                            <div class="flex flex-row space-between align-center gap-10">
                                                                <img src="assets/images/icons/employees-icon.png"
                                                                    alt="Employees" height="24">
                                                                <p style="margin: 0">
                                                                    <?php echo $department['active_employee_count'] ?>
                                                                </p>
                                                            </div>

                                                            <div class="flex flex-row space-between align-center gap-10">
                                                                <img src="assets/images/icons/work-icon.png" alt="Employees"
                                                                    height="24">
                                                                <p style="margin: 0">
                                                                    <?php echo $department['active_job_count'] ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="flex flex-row flex-end gap-10">
                                                            <?php
                                                            if ($department['department_status'] == 'Active') {
                                                                echo '<div class="job-status-active">' . htmlspecialchars($department["department_status"]) . '</div>';
                                                            } else {
                                                                echo '<div class="job-status-inactive">' . htmlspecialchars($department["department_status"]) . '</div>';
                                                            }
                                                            ?>
                                                            <div>
                                                                <button id="job-edit-button">
                                                                    <img src="assets/images/icons/more-icon.png"
                                                                        height="16px" alt="">
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="flex flex-column justify-center align-center gap-10"
                                                        style="flex-direction: column;">
                                                        <div class="font-bold" style="text-align: center;">
                                                            <?= htmlspecialchars($department["department_name"]); ?>
                                                        </div>
                                                        <div style="color:var(--color-base-content)">
                                                            <?= htmlspecialchars($department["branch"]); ?>
                                                        </div>
                                                    </div>

                                                    <div class="flex flex-row space-between other-job-info">
                                                        <div class="flex flex-row space-between gap-20">
                                                            <div style="width:100%;">
                                                                <p class="font-bold" style="margin: 0">Manager:</p>
                                                                <?php if (isset($department["manager_first_name"]) && isset($department["manager_last_name"])): ?>
                                                                    <?= htmlspecialchars($department["manager_first_name"] . " " . $department['manager_last_name']); ?>
                                                                <?php else: ?>
                                                                    Not Assigned
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>

                                                        <div class="flex flex-column justify-center align-start">
                                                            <div>
                                                                <p class="font-bold" style="margin: 0">Team Leader:</p>
                                                                <?php if (isset($department["team_leader_first_name"]) && isset($department["team_leader_last_name"])): ?>
                                                                    <?= htmlspecialchars($department["team_leader_first_name"] . " " . $department['team_leader_last_name']); ?>
                                                                <?php else: ?>
                                                                    Not Assigned
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <dialog id="job-detail-dialog" style="width: 500px; padding: 20px">
        <div class="dialog-content">
            <div class="flex flex-row space-between align-center">
                <div>
                    <h2>Department Details</h2>
                </div>
                <div>
                    <div id="modal-close-button" style="border: 1px solid black; border-radius: 50%"
                        onclick="closeDetailDialog()"><img width="24" src="assets/images/icons/close-icon.png" alt="">
                    </div>
                </div>
            </div>
            <form id="job-details-form" action="departments.php" method="POST"
                class="flex flex-column gap-20 justify-center align-center">
                <div class="flex flex-row flex-wrap gap-20 space-between ">
                    <div>
                        <label for="department_id" style="display:block; margin-bottom: 5px;">Department ID</label>
                        <input class="font-size-16" type="text" id="department_id" name="department_id" readonly>
                    </div>

                    <div>
                        <label for="department_name" style="display:block; margin-bottom: 5px;">Department Name</label>
                        <input class="font-size-16" type="text" id="department_name" name="department_name">
                    </div>

                    <div>
                        <label for="branch" style="display:block; margin-bottom: 5px;">Branch</label>
                        <input class="font-size-16" type="text" id="branch" name="branch">
                    </div>

                    <div>
                        <label for="team_leader_id" style="display:block; margin-bottom: 5px;">Team Leader</label>
                        <select name="team_leader_id" style="width: 215px; margin: 0; font-size: 16px; padding: 12px">
                            <?php foreach ($employees as $employee) { ?>
                                <option value="<?= htmlspecialchars($employee['employee_id']) ?>">
                                    <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div>
                        <label for="manager_id" style="display:block; margin-bottom: 5px;">Manager</label>
                        <select name="manager_id" style="width: 215px; margin: 0; font-size: 16px; padding: 12px">
                            <?php foreach ($employees as $employee) { ?>
                                <option value="<?= htmlspecialchars($employee['employee_id']) ?>">
                                    <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div>
                        <label for="status" style="display:block; margin-bottom: 5px;">Status</label>
                        <select name="status" style="width: 215px; margin: 0;font-size: 16px; padding: 12px">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div>
                    <button id="delete" type="submit" class="delete-button"
                        style="padding: 10px 5px; width: 180px; height: 40px;" name="delete">Delete</button>

                    <button id="edit-department-detail" type="submit"
                        style="padding: 10px 5px; width: 180px; height: 40px;"
                        name="edit-department-detail">Edit</button>
                </div>
            </form>
        </div>
    </dialog>

    <dialog id="add-job-dialog" style="width: 500px; padding: 20px">
        <div class="flex flex-column flex-start">
            <div>
                <h2>Add Department</h2>
            </div>
            <form action="departments.php" method="POST">
                <div class="flex flex-row flex-wrap gap-20 space-between ">

                    <div>
                        <label for="department_name" style="display:block; margin-bottom: 5px;">Department Name</label>
                        <input class="font-size-16" type="text" id="department_name" name="department_name">
                    </div>

                    <div>
                        <label for="branch" style="display:block; margin-bottom: 5px;">Branch</label>
                        <input class="font-size-16" type="text" id="branch" name="branch">
                    </div>

                    <div>
                        <label for="team_leader_id" style="display:block; margin-bottom: 5px;">Team Leader</label>
                        <select name="team_leader_id" style="width: 215px; margin: 0; font-size: 16px; padding: 12px">
                            <?php foreach ($employees as $employee) { ?>
                                <option value="<?= htmlspecialchars($employee['employee_id']) ?>">
                                    <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div>
                        <label for="manager_id" style="display:block; margin-bottom: 5px;">Manager</label>
                        <select name="manager_id" style="width: 215px; margin: 0; font-size: 16px; padding: 12px">
                            <?php foreach ($employees as $employee) { ?>
                                <option value="<?= htmlspecialchars($employee['employee_id']) ?>">
                                    <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div>
                        <label for="status" style="display:block; margin-bottom: 5px;">Status</label>
                        <select name="status" style="width: 215px; margin: 0;font-size: 16px; padding: 12px">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
        </div>
        <div class="flex justify-center align-center">
            <button id="add-department" type="submit" style="padding: 20px 10px; width: 180px; height: 50px;"
                name="add-department">Add Department</button>

        </div>
        </form>
    </dialog>

</body>

<script>
    function openPage(evt, pageName, color) {
        var i, tabcontent, tablinks;

        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
            tabcontent[i].classList.remove("active");
        }

        tablinks = document.getElementsByClassName("tablink");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].style.backgroundColor = "";
            tablinks[i].classList.remove("active");
            tablinks[i].setAttribute("tabindex", "-1");
        }

        var currentTab = document.getElementById(pageName);
        currentTab.style.display = "block";
        currentTab.classList.add("active");

        evt.currentTarget.style.backgroundColor = color;
        evt.currentTarget.classList.add("active");
        evt.currentTarget.setAttribute("tabindex", "0");
    }

    document.addEventListener("DOMContentLoaded", function () {
        document.getElementById("defaultOpen").click();
    });


    const job_dialog = document.getElementById('job-detail-dialog');

    function showDetailDialog(id) {
        const currentRow = document.querySelector(`#job-${id}`);

        let jobData;
        try {
            jobData = JSON.parse(currentRow.dataset.job);
        } catch (error) {
            console.error("Error parsing JSON data from row dataset:", error);
            return;
        }
        console.log(jobData)

        Object.keys(jobData).forEach(key => {
            const inputElement = document.querySelector(`input[name="${key}"]`);
            if (inputElement) {
                inputElement.value = jobData[key];
            }

            const salaryFrequencySelect = document.querySelector(`select[name="salary_frequency"]`);
            if (salaryFrequencySelect && jobData.salary_frequency) {
                salaryFrequencySelect.value = jobData.salary_frequency;
            }

            const selectElement = document.querySelector(`select[name="${key}"]`);
            if (selectElement) {
                selectElement.value = jobData[key];
            }
        });

        job_dialog.showModal();
        job_dialog.addEventListener('click', function (event) {
            if (event.target === job_dialog) {
                closeDetailDialog();
            }
        });
    }

    function closeDetailDialog() {
        job_dialog.close();
    }

    const form = document.getElementById('job-details-form');
    form.addEventListener('submit', () => {
        const disabledField = form.querySelector('[name="job_id"]');
        disabledField.disabled = false;
    });

    const add_job = document.getElementById('add-job-dialog');

    function addJobModal() {

        add_job.showModal()

        add_job.addEventListener('click', function (event) {
            if (event.target === add_job) {
                closeAddDialog();
            }
        });
    }

    function closeAddDialog() {
        console.log(1)
        add_job.close();
    }
</script>


</html>