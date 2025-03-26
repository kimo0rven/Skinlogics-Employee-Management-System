<?php
session_start();
if (isset($_SESSION['loggedin']) || $_SESSION['loggedin'] == true) {
    $user_account_id = $_SESSION["user_account_id"];

    $accountType = $_SESSION["account_type"];

    include 'includes/database.php';
    include 'config.php';

    try {
        $sql = "SELECT * FROM employee WHERE user_account_id = :user_account_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_account_id', $user_account_id, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) {

            foreach ($rows as $row) {
                $first_name = $row['first_name'];
                $last_name = $row['last_name'];
            }
        } else {
            echo "No records found for user account ID: " . $user_account_id;
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    try {
        $sql_employees = "SELECT
    e.employee_id,
	e.status,
    e.first_name,
    e.last_name,
    e.job_id,
    j.job_name,
    e.department_id,
    d.department_name,
    d.branch AS department_branch, 
    e.email,
    e.mobile,
    e.user_account_id,
    ua.avatar
    FROM
        employee e
    LEFT JOIN
        job j ON e.job_id = j.job_id
    LEFT JOIN
        department d ON e.department_id = d.department_id
    LEFT JOIN
        user_account ua ON e.user_account_id = ua.user_account_id;";
        $stmt_employees = $pdo->prepare($sql_employees);
        $stmt_employees->execute();
        $employees = $stmt_employees->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error fetching employee data: " . $e->getMessage();
    }

}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> | Employees</title>
    <link rel="stylesheet" href="style.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.min.js"></script>
</head>

<body class="font-medium">
    <div id="admin">
        <div class="dashboard-background">
            <div class="dashboard-container">
                <div class="dashboard-navigation">
                    <div class="navigation-container ">
                        <div><a href="/dashboard.php"><img src="assets/images/icons/dashboard-icon.png" alt=""></a>
                        </div>
                        <div class="active"><a href="#"><img src="assets/images/icons/employee-icon.png" alt=""></a>
                        </div>
                        <div>3</div>
                        <div>4</div>
                        <div>5</div>
                        <div>6</div>
                    </div>

                </div>
                <div class="dashboard-content">
                    <div class="dashboard-content-item1">
                        <div class="dashboard-content-header font-black">
                            <h1>EMPLOYEES</h1>
                        </div>
                        <div id="logout-admin" class="dashboard-content-header font-medium">
                            <p><?php echo $first_name . " " . $last_name ?></p>
                            <img class="dashboard-content-header-img profile-dropdown-trigger"
                                src="assets/images/avatars/<?php echo $_SESSION['avatar'] ?>" alt="">

                        </div>
                    </div>

                    <div class="employee-main-content">
                        <div class="employee-header">
                            <button>add employee</button>
                        </div>

                        <div class="employee-display">
                            <table>
                                <thead class="font-bold">
                                    <tr>
                                        <th>Avatar</th>
                                        <th>Name</th>
                                        <th>Job Title</th>
                                        <th>Department</th>
                                        <th>Branch</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>Status</th>
                                        <th>Edit</th>
                                    </tr>
                                </thead>
                                <tbody font-medium>
                                    <?php if (isset($employees) && !empty($employees)): ?>
                                        <?php foreach ($employees as $employee): ?>

                                            <tr class="employee-display-list text-center">
                                                <td>
                                                    <img class="margin-right: 10px"
                                                        src="assets/images/avatars/<?php echo !empty($employee['avatar']) ? $employee['avatar'] : 'default.png'; ?>"
                                                        alt="">
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($employee['first_name']) . " " . htmlspecialchars($employee['last_name']); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($employee['job_name']); ?></td>
                                                <td><?php echo htmlspecialchars($employee['department_name']); ?></td>
                                                <td><?php echo htmlspecialchars($employee['department_branch']); ?></td>
                                                <td><?php echo htmlspecialchars($employee['email']); ?></td>
                                                <td><?php echo htmlspecialchars($employee['mobile']); ?></td>
                                                <td><?php echo htmlspecialchars($employee['status']); ?></td>
                                                <td><img id="<?php echo json_encode($employee['employee_id']); ?>"
                                                        src="assets/images/icons/edit-icon.png" alt=""
                                                        onclick="openModal(this)">
                                                </td>
                                            </tr>


                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7">No employees found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <dialog id="myModal">

            <span id="modalEmployeeId"></span>
            <h2>Modal Title</h2>
            <p>This is the content of the modal dialog.</p>
            <p>Employee ID: <span id="modalEmployeeId"></span></p>
            <input class="login-fields" type="text" id="username" name="username">
            <button class="close-button" onclick="closeModal()">Close</button>
        </dialog>
</body>


<script>
    document.getElementById("logout-admin").addEventListener("click", function () {
        window.location.href = "logout.php";
    });

    const modal = document.getElementById('myModal');
    const modalEmployeeIdSpan = document.getElementById('modalEmployeeId');

    function openModal(employeeId) {
        console.log(employeeId.id)
        const employeeIdFromButton = employeeId.id;
        modal.showModal(employeeId.id);
        modalEmployeeIdSpan.textContent = employeeIdFromButton;
    }

    function closeModal() {
        modal.close();
    }

</script>

</html>