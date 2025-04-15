<?php
session_start();

if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
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
        if (strtolower(trim($searchTerm)) === "active" || strtolower(trim($searchTerm)) === "inactive") {

            $sql = "SELECT job.*, department.department_name 
                    FROM job 
                    INNER JOIN department 
                        ON job.department_id = department.department_id 
                    WHERE job.status = :status";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':status', ucfirst(strtolower($searchTerm)), PDO::PARAM_STR);
        } else {

            $sql = "SELECT job.*, department.department_name 
                    FROM job 
                    INNER JOIN department 
                        ON job.department_id = department.department_id 
                    WHERE job.job_name LIKE :search 
                        OR job.description LIKE :search 
                        OR CAST(job.salary AS CHAR) LIKE :search 
                        OR job.salary_frequency LIKE :search 
                        OR job.status LIKE :search";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':search', "%" . $searchTerm . "%", PDO::PARAM_STR);
        }
    } else {

        $sql = "SELECT job.*, department.department_name 
                FROM job 
                INNER JOIN department 
                    ON job.department_id = department.department_id";
        $stmt = $pdo->prepare($sql);
    }

    $stmt->execute();
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error loading jobs: " . $e->getMessage());
}

$departmentSearch = '';
if (isset($_GET['department_search'])) {
    $departmentSearch = trim($_GET['department_search']);
}

try {
    if (!empty($departmentSearch)) {
        $sql = "SELECT 
                    d.*, 
                    m.first_name AS manager_first_name, 
                    m.last_name AS manager_last_name, 
                    t.first_name AS team_leader_first_name, 
                    t.last_name AS team_leader_last_name,
                    COUNT(DISTINCT j.job_id) AS job_count,
                    (
                    SELECT COUNT(DISTINCT e.employee_id)
                    FROM employee e
                    WHERE e.department_id = d.department_id
                        AND e.status = 'Active'
                    ) AS employee_count
                FROM department d
                LEFT JOIN employee m ON d.manager_id = m.employee_id
                LEFT JOIN employee t ON d.team_leader_id = t.employee_id
                LEFT JOIN job j ON d.department_id = j.department_id
                WHERE d.department_name LIKE :search 
                OR m.first_name LIKE :search 
                OR m.last_name LIKE :search 
                OR t.first_name LIKE :search 
                OR t.last_name LIKE :search
                GROUP BY d.department_id";
        $stmt_dept = $pdo->prepare($sql);
        $stmt_dept->bindValue(':search', "%" . $departmentSearch . "%", PDO::PARAM_STR);
    } else {
        $sql = "SELECT 
                    d.*, 
                    m.first_name AS manager_first_name, 
                    m.last_name AS manager_last_name, 
                    t.first_name AS team_leader_first_name, 
                    t.last_name AS team_leader_last_name,
                    COUNT(DISTINCT j.job_id) AS job_count,
                    (
                    SELECT COUNT(DISTINCT e.employee_id)
                    FROM employee e
                    WHERE e.department_id = d.department_id
                        AND e.status = 'Active'
                    ) AS employee_count
                FROM department d
                LEFT JOIN employee m ON d.manager_id = m.employee_id
                LEFT JOIN employee t ON d.team_leader_id = t.employee_id
                LEFT JOIN job j ON d.department_id = j.department_id
                GROUP BY d.department_id";
        $stmt_dept = $pdo->prepare($sql);
    }

    $stmt_dept->execute();
    $departmentsAll = $stmt_dept->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error loading departments: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['edit-job-detail'])) {
        $sql = 'UPDATE job
        SET job_name = :job_name, 
            description = :description, 
            salary = :salary, 
            salary_frequency = :salary_frequency, 
            status = :status
        WHERE job_id = :job_id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':job_name' => $_POST['job_name'],
            ':description' => $_POST['description'],
            ':salary' => $_POST['salary'],
            ':salary_frequency' => $_POST['salary_frequency'],
            ':status' => $_POST['status'],
            ':job_id' => $_POST['job_id'],
        ]);

    }

    if (isset($_POST['add-job'])) {

        print_r($_POST);
        $sql = 'INSERT INTO job 
        (job_name, description, salary, salary_frequency, status) 
        VALUES (:job_name, :description, :salary, :salary_frequency, :status)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':job_name' => $_POST['job_name'],
            ':description' => $_POST['description'],
            ':salary' => $_POST['salary'],
            ':salary_frequency' => $_POST['salary_frequency'],
            ':status' => $_POST['status']
        ]);
    }
    header("Location: work.php");

}

try {
    $sql = "SELECT * FROM department";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("" . $e->getMessage());
}
?>

<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Admin'; ?> | Employees</title>
    <link rel="stylesheet" href="style.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.min.js"></script>
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
                        <h1>JOBS & DEPARMENTS</h1>
                    </div>
                    <div id="logout-admin" class="dashboard-content-header font-medium">
                        <?php include('includes/header-avatar.php') ?>
                    </div>
                </div>

                <div class="employee-main-content" style="padding: 0; border: 0;">
                    <div class="employee-display" style="background: var(--color-base-200); border: 0">

                        <!-- <div role="tablist" aria-label="Sample Tabs" class="tablinks-container">
                            <button id="defaultOpen" class="tablink" role="tab" aria-controls="Tab1"
                                onclick="openPage(event, 'Tab1')" tabindex="-1">
                                Jobs
                            </button>
                            <button class="tablink active" role="tab" aria-controls="Tab2"
                                onclick="openPage(event, 'Tab2')" tabindex="0">
                                Departments
                            </button>
                        </div> -->

                        <div class="jobs-bottom-container">
                            <div id="Tab1" class="tabcontent" role="tabpanel">
                                <div class="job-display-container">
                                    <div class="flex flex-row space-between align-center">

                                        <form class="flex flex-row" method="GET" action="work.php" style="margin:0">
                                            <input type="text" id="job_search" name="search"
                                                placeholder="Search jobs..."
                                                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">

                                            <div><img height="32px" width="32px"
                                                    src="assets/images/icons/search-icon.png" alt="Search">
                                            </div>

                                            <div>
                                                <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                                                    <a href="work.php" class="clear-search">Clear Search</a>
                                                <?php endif; ?>
                                            </div>
                                        </form>

                                        <div class="font-bold font-size-20">
                                            <?php
                                            $jobCount = isset($jobs) ? count($jobs) : 0;
                                            echo $jobCount . " Job" . ($jobCount != 1 ? "s" : "");
                                            ?>
                                        </div>

                                        <div><button id="add-job-btn" onclick="addJobModal()">Add Job</button></div>

                                    </div>
                                    <div class="jobs-list-container">
                                        <div id="search-results" class="jobs-list">
                                            <?php
                                            foreach ($jobs as $job) {
                                                ?>
                                                <div id="job-<?php echo json_encode($job['job_id']); ?>" class="job-record"
                                                    onclick="showDetailDialog(<?php echo json_encode($job['job_id']) ?>)"
                                                    data-job='<?php echo json_encode($job); ?>'>
                                                    <div id="job-display-top" class="flex flex-row flex-end gap-10">
                                                        <?php
                                                        if ($job['status'] == 'Active') {
                                                            echo '<div class="job-status-active">' . htmlspecialchars($job["status"]) . '</div>';
                                                        } else {
                                                            echo '<div class="job-status-inactive">' . htmlspecialchars($job["status"]) . '</div>';
                                                        }
                                                        ?>
                                                        <div>
                                                            <button id="job-edit-button">
                                                                <img src="assets/images/icons/more-icon.png" height="16px"
                                                                    alt="">
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="flex flex-column justify-center align-center gap-10">
                                                        <div class="font-bold" style="text-align: center;">
                                                            <?= htmlspecialchars($job["job_name"]); ?>
                                                        </div>
                                                        <div style="color:var(--color-base-content)">
                                                            <?= htmlspecialchars($job["department_name"]); ?>
                                                        </div>
                                                    </div>

                                                    <div class="other-job-info">
                                                        <div class="flex flex-row space-between gap-20">
                                                            <div style="width:100%;">
                                                                <p class="font-bold" style="margin: 0">Salary:</p>
                                                                PHP<?= number_format($job["salary"], 2); ?>
                                                            </div>
                                                            <div style="width:100%;">
                                                                <p class="font-bold" style="margin: 0">Frequency:</p>
                                                                <?= htmlspecialchars($job["salary_frequency"]); ?>
                                                            </div>
                                                        </div>

                                                        <div class="flex flex-column justify-center align-start">
                                                            <div><?= htmlspecialchars($job["description"]); ?></div>
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
                            <!-- <div id="Tab2" class="tabcontent active" role="tabpanel">
                                <div class="job-display-container">
                                    <div class="flex flex-row space-between align-center">

                                        <form class="flex flex-row" method="GET" action="work.php" style="margin:0">
                                            <input type="text" id="department_search" name="department_search"
                                                placeholder="Search departments..."
                                                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">

                                            <div><img height="32px" width="32px"
                                                    src="assets/images/icons/search-icon.png" alt="Search">
                                            </div>

                                            <div>
                                                <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                                                    <a href="work.php" class="clear-search">Clear Search</a>
                                                <?php endif; ?>
                                            </div>
                                        </form>

                                        <div class="font-bold font-size-20">
                                            <?php echo isset($departmentsAll) ? count($departmentsAll) : 0; ?>
                                            Departments
                                        </div>

                                        <div><button id="add-job-btn" onclick="addJobModal()">Add
                                                Department</button></div>

                                    </div>
                                    <div class="jobs-list-container">
                                        <div class="jobs-list">
                                            <?php
                                            foreach ($departmentsAll as $dept) {
                                                ?>
                                                <div id="job-<?php echo json_encode($dept['department_id']); ?>"
                                                    class="job-record"
                                                    onclick="showDetailDialog(<?php echo json_encode($dept['department_id']) ?>)"
                                                    data-job='<?php echo json_encode($dept); ?>'>
                                                    <div id="job-display-top" class="flex flex-row flex-end gap-10">
                                                        <?php
                                                        if ($job['status'] == 'Active') {
                                                            echo '<div class="job-status-active">' . htmlspecialchars($dept["status"]) . '</div>';
                                                        } else {
                                                            echo '<div class="job-status-inactive">' . htmlspecialchars($dept["status"]) . '</div>';
                                                        }
                                                        ?>
                                                        <div>
                                                            <button id="job-edit-button">
                                                                <img src="assets/images/icons/more-icon.png" height="16px"
                                                                    alt="">
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="flex flex-column justify-center align-center gap-10">
                                                        <div class="font-bold" style="text-align: center;">
                                                            <?= htmlspecialchars($dept["department_name"]); ?>
                                                        </div>
                                                        <div style="color:var(--color-base-content)">
                                                            <?= htmlspecialchars($dept["branch"]); ?>
                                                        </div>
                                                    </div>

                                                    <div class="other-job-info">
                                                        <div class="department-info-row">
                                                            <div class="department-info-col">
                                                                <p class="department-info-title">Team Leader:</p>
                                                                <p class="department-info-value">
                                                                    <?= htmlspecialchars($dept["team_leader_first_name"] . " " . $dept["team_leader_last_name"]); ?>
                                                                </p>
                                                            </div>
                                                            <div class="department-info-col">
                                                                <p class="department-info-title">Manager:</p>
                                                                <p class="department-info-value">
                                                                    <?= htmlspecialchars($dept["manager_first_name"] . " " . $dept["manager_last_name"]); ?>
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <div class="department-info-row">
                                                            <div class="department-info-col">
                                                                <p class="department-info-title">Jobs:</p>
                                                                <p class="department-info-value">
                                                                    <?= htmlspecialchars($dept["job_count"]); ?>
                                                                </p>
                                                            </div>
                                                            <div class="department-info-col">
                                                                <p class="department-info-title">Employees:</p>
                                                                <p class="department-info-value">
                                                                    <?= htmlspecialchars($dept["employee_count"]); ?>
                                                                </p>
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
                            </div> -->

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
                    <h2>Job Details</h2>
                </div>
                <div>
                    <div id="modal-close-button" style="border: 1px solid black; border-radius: 50%"
                        onclick="closeDetailDialog()"><img width="24" src="assets/images/icons/close-icon.png" alt="">
                    </div>
                </div>
            </div>
            <form id="job-details-form" action="work.php" method="POST"
                class="flex flex-column gap-20 justify-center align-center">
                <div class="flex flex-row flex-wrap gap-20 space-between ">
                    <div>
                        <label for="job_id" style="display:block; margin-bottom: 5px;">Job ID</label>
                        <input class="font-size-16" type="text" id="job_id" name="job_id" disabled>
                    </div>

                    <div>
                        <label for="job_name" style="display:block; margin-bottom: 5px;">Job Name</label>
                        <input class="font-size-16" type="text" id="job_name" name="job_name">
                    </div>

                    <div>
                        <label for="description" style="display:block; margin-bottom: 5px;">Description</label>
                        <input class="font-size-16" type="text" id="description" name="description">
                    </div>

                    <div>
                        <label for="salary" style="display:block; margin-bottom: 5px;">Salary</label>
                        <input class="font-size-16" type="text" id="salary" name="salary">
                    </div>

                    <div>
                        <label for="salary_frequency" style="display:block; margin-bottom: 5px;">Salary
                            Frequency</label>
                        <select class="font-size-16" name="salary_frequency"
                            style="width: 215px; margin: 0;font-size: 16px; padding: 12px">
                            <option value=" Weekly">Weekly</option>
                            <option value="Bi-Weekly">Bi-Weekly</option>
                            <option value="Monthly">Monthly</option>
                            <option value="Yearly">Yearly</option>
                        </select>
                    </div>

                    <div>
                        <label for="department_id" style="display:block; margin-bottom: 5px;">Department</label>
                        <select name="department_id" id="department_id"
                            style="width: 215px; margin: 0; font-size: 16px; padding: 12px">
                            <?php
                            // Get the default department from the first job record (if available)
                            $defaultDepartmentId = isset($jobs[0]['department_id']) ? $jobs[0]['department_id'] : null;

                            foreach ($departments as $department) {
                                $selected = ($department['department_id'] == $defaultDepartmentId) ? ' selected' : '';
                                echo '<option value="' . htmlspecialchars($department['department_id']) . '"' . $selected . '>' . htmlspecialchars($department['department_name']) . '</option>';
                            }
                            ?>
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
                    <button id="edit-job-detail" type="submit" style="padding: 20px 10px; width: 180px; height: 50px;"
                        name="edit-job-detail">Edit</button>
                </div>
            </form>
        </div>
    </dialog>

    <dialog id="add-job-dialog" style="width: 500px; padding: 20px">
        <div class="flex flex-column flex-start">
            <div>
                <h2>Job Details</h2>
            </div>
            <form action="work.php" method="POST">
                <div class="flex flex-row flex-wrap space-between gap-20">
                    <div>
                        <label for="job_name" style="display:block; margin-bottom: 5px;">Job Name</label>
                        <input class="font-size-16" type="text" id="job_name" name="job_name">
                    </div>

                    <div>
                        <label for="description" style="display:block; margin-bottom: 5px;">Description</label>
                        <input class="font-size-16" type="text" id="description" name="description">
                    </div>

                    <div>
                        <label for="salary" style="display:block; margin-bottom: 5px;">Salary</label>
                        <input class="font-size-16" type="text" id="salary" name="salary">
                    </div>

                    <div>
                        <label for="salary_frequency" style="display:block; margin-bottom: 5px;">Salary
                            Frequency</label>
                        <select class="font-size-16" name="salary_frequency"
                            style="width: 215px; margin: 0;font-size: 16px; padding: 12px">
                            <option value=" Weekly">Weekly</option>
                            <option value="Bi-Weekly">Bi-Weekly</option>
                            <option value="Monthly">Monthly</option>
                            <option value="Yearly">Yearly</option>
                        </select>
                    </div>

                    <div>
                        <label for="status" style="display:block; margin-bottom: 5px;">Department</label>
                        <select name="status" style="width: 215px; margin: 0; font-size: 16px; padding: 12px">
                            <?php foreach ($departments as $department) {
                                echo '<option value="' . htmlspecialchars($department['department_id']) . '">' . htmlspecialchars($department['department_name']) . '</option>';
                            } ?>
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
            <button id="add-job" type="submit" style="padding: 20px 10px; width: 180px; height: 50px;"
                name="add-job">Add Job</button>
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