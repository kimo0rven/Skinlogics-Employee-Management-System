<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    $user_account_id = $_SESSION["user_account_id"];
    $accountType = $_SESSION["account_type"];

    include 'includes/database.php';
    include 'config.php';

    $first_name = '';
    $last_name = '';

    try {
        $sql = "SELECT first_name, last_name FROM employee WHERE user_account_id = :user_account_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_account_id', $user_account_id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $first_name = $row['first_name'];
            $last_name = $row['last_name'];
        } else {
            echo "No records found for user account ID: " . $user_account_id;
        }
    } catch (PDOException $e) {
        echo "Error fetching user information: " . $e->getMessage();
    }

    $employees = [];
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
            user_account ua ON e.user_account_id = ua.user_account_id";
        $stmt_employees = $pdo->prepare($sql_employees);
        $stmt_employees->execute();
        $employees = $stmt_employees->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error fetching employee data: " . $e->getMessage();
    }

    if (isset($_POST['editEmployee'])) {
        $employee_id_to_edit = $_POST['employee_id'];
        $new_first_name = $_POST['first_name'];
        $new_last_name = $_POST['last_name'];
    }


} else {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Admin'; ?> | Employees</title>
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
                            <p><?php echo htmlspecialchars($first_name) . " " . htmlspecialchars($last_name); ?></p>
                            <img class="dashboard-content-header-img profile-dropdown-trigger"
                                src="assets/images/avatars/<?php echo $_SESSION['avatar'] ?? 'default.png'; ?>" alt="">
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
                                    <?php if (!empty($employees)): ?>
                                        <?php foreach ($employees as $employee): ?>
                                            <tr id="row-<?php echo $employee['employee_id'] ?>"
                                                data-user='<?php echo json_encode($employee); ?>'
                                                class="employee-display-list text-center">
                                                <td>
                                                    <img class="margin-right: 10px"
                                                        src="assets/images/avatars/<?php echo !empty($employee['avatar']) ? htmlspecialchars($employee['avatar']) : 'default.png'; ?>"
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
                                                <td><img class="edit-icon" src="assets/images/icons/edit-icon.png" alt=""
                                                        onclick="openModal(<?php echo $employee['employee_id'] ?>)">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9">No employees found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                        </div>

                    </div>
                </div>
            </div>
        </div>

        <dialog style="border-radius: 10px; border: 1px solid #D1D1D1; width: 70%;" id="myModal">
            <h2 class="text-capitalize">Edit Employee Details</h2>
            <div id="modal-content" class="flex flex-row employee-detail">

                <form action="" method="post">
                    <label for="employee_id">Employee ID</label>
                    <input type="text" name="employee_id">
                    <label for="first_name">First Name</label>
                    <input type="text" name="first_name">
                    <label for="last_name">First Name</label>
                    <input type="text" name="last_name">
                    <input type="text" name="job_id">
                    <br>
                    <button type="submit" name="editEmployee">submit</button>
                </form>
            </div>
            <button class="close-button" onclick="closeModal()">Close</button>
        </dialog>
    </div>
</body>

<script>
    const modal = document.getElementById('myModal');
    const modalContentDiv = document.getElementById('modal-content');

    document.getElementById("logout-admin").addEventListener("click", function () {
        window.location.href = "logout.php";
    });

    // async function sendPostRequest(apiUrl, data) {
    //     try {
    //         const response = await fetch(apiUrl, {
    //             method: 'POST',
    //             headers: {
    //                 'Content-Type': 'application/json',
    //             },
    //             body: JSON.stringify(data),
    //         });

    //         if (!response.ok) {
    //             throw new Error(`HTTP error! status: ${response.status}`);
    //         }

    //         const responseData = await response.json();
    //         console.log('Success:', responseData);
    //         return responseData;

    //     } catch (error) {
    //         console.error('Error sending POST request:', error);
    //         return null;
    //     }
    // }

    function openModal(id) {
        const currentRow = document.querySelector(`#row-${id}`);
        const data = JSON.parse(currentRow.dataset.user);

        Object.keys(data).forEach(key => {
            console.log(key);
            const inputElement = document.querySelector(`dialog form input[name="${key}"]`);
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

</html>