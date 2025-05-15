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
        if (strtolower(trim($searchTerm)) === "active" || strtolower(trim($searchTerm)) === "inactive") {

            $sql = "SELECT job.*, department.department_name,
                    COUNT(CASE WHEN employee.status = 'Active' THEN employee.employee_id END) AS active_employee_count
                    FROM job
                    INNER JOIN department
                    ON job.department_id = department.department_id
                    LEFT JOIN employee ON job.job_id = employee.job_id
                    WHERE job.status = :status
                    GROUP BY job.job_id, job.job_name, job.description, job.salary, job.salary_frequency, job.status, job.department_id, department.department_name";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':status', ucfirst(strtolower($searchTerm)), PDO::PARAM_STR);
        } else {

            $sql = "SELECT job.*, department.department_name,
                    COUNT(CASE WHEN employee.status = 'Active' THEN employee.employee_id END) AS active_employee_count
                    FROM job
                    INNER JOIN department
                    ON job.department_id = department.department_id
                    LEFT JOIN employee ON job.job_id = employee.job_id
                    WHERE job.job_name LIKE :search
                    OR job.description LIKE :search
                    OR CAST(job.salary AS CHAR) LIKE :search
                    OR job.salary_frequency LIKE :search
                    OR job.status LIKE :search
                    GROUP BY job.job_id, job.job_name, job.description, job.salary, job.salary_frequency, job.status, job.department_id, department.department_name";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':search', "%" . $searchTerm . "%", PDO::PARAM_STR);
        }
    } else {

        $sql = "SELECT job.*, department.department_name,
                COUNT(CASE WHEN employee.status = 'Active' THEN employee.employee_id END) AS active_employee_count
                FROM job
                INNER JOIN department
                    ON job.department_id = department.department_id
                LEFT JOIN employee ON job.job_id = employee.job_id
                GROUP BY job.job_id, job.job_name, job.description, job.salary, job.salary_frequency, job.status, job.department_id, department.department_name";
        $stmt = $pdo->prepare($sql);
    }

    $stmt->execute();
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error loading jobs: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['edit-job-detail'])) {
        $sql = 'UPDATE job
        SET job_name = :job_name, 
            description = :description, 
            salary = :salary, 
            salary_frequency = :salary_frequency, 
            status = :status,
            department_id = :department_id
        WHERE job_id = :job_id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':job_name' => $_POST['job_name'],
            ':description' => $_POST['description'],
            ':salary' => $_POST['salary'],
            ':salary_frequency' => $_POST['salary_frequency'],
            ':status' => $_POST['status'],
            ':job_id' => $_POST['job_id'],
            ':department_id' => $_POST['department_id']

        ]);

    }

    if (isset($_POST['add-job'])) {
        print_r($_POST);
        $sql = 'INSERT INTO job 
        (job_name, description, salary, salary_frequency, status,department_id) 
        VALUES (:job_name, :description, :salary, :salary_frequency, :status, :department_id)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':job_name' => $_POST['job_name'],
            ':description' => $_POST['description'],
            ':salary' => $_POST['salary'],
            ':salary_frequency' => $_POST['salary_frequency'],
            ':status' => $_POST['status'],
            ':department_id' => $_POST['department_id']
        ]);
    }

    if (isset($_POST['delete'])) {
        $sql = 'DELETE FROM job WHERE job_id = :job_id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':job_id' => $_POST['job_id'],
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
    <title><?php echo isset($title) ? $title : 'Admin'; ?> | Jobs</title>
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
                        <h1>JOBS</h1>
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
                                                    <div id="job-display-top" class="flex flex-row space-between gap-10">
                                                        <div class="flex flex-row align-center gap-10"> <img
                                                                src="assets/images/icons/employees-icon.png" alt="Employees"
                                                                height="24">
                                                            <p style="margin: 0">
                                                                <?php echo $job['active_employee_count']; ?>
                                                            </p>
                                                        </div>
                                                        <div class="flex flex-row flex-end gap-10">
                                                            <?php
                                                            if ($job['status'] == 'Active') {
                                                                echo '<div class="job-status-active">' . htmlspecialchars($job["status"]) . '</div>';
                                                            } else {
                                                                echo '<div class="job-status-inactive">' . htmlspecialchars($job["status"]) . '</div>';
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
            <form id="job-details-form" action="#" method="POST"
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
                <div class="flex flex-row gap-20">
                    <button id="delete" type="submit" class="delete-button"
                        style="padding: 10px 5px; width: 180px; height: 40px;" name="delete">Delete</button>

                    <button id="edit-job-detail" type="submit" style="padding: 20px 10px; width: 180px; height: 40px;"
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
            <form action="#" method="POST">
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
                        <label for="department_id" style="display:block; margin-bottom: 5px;">Department</label>
                        <select name="department_id" style="width: 215px; margin: 0; font-size: 16px; padding: 12px">
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