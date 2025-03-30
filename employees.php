<?php
session_start();

if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

$user_account_id = (int) $_SESSION["user_account_id"];
$accountType = $_SESSION["account_type"] ?? '';

include 'includes/database.php';
include 'config.php';


$first_name = '';
$last_name = '';

try {
    $sql = "SELECT first_name, last_name FROM employee WHERE user_account_id = :user_account_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_account_id' => $user_account_id]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userInfo) {
        $first_name = $userInfo['first_name'];
        $last_name = $userInfo['last_name'];
    } else {
        echo "No records found for user account ID: " . htmlspecialchars($user_account_id);
    }
} catch (PDOException $e) {
    echo "Error fetching user information: " . $e->getMessage();
}

$employees = [];
try {
    $sql_employees = "SELECT
            e.*,
            j.job_name,
            d.department_name,
            d.branch AS department_branch,
            ua.avatar
        FROM employee e
        LEFT JOIN job j ON e.job_id = j.job_id
        LEFT JOIN department d ON e.department_id = d.department_id
        LEFT JOIN user_account ua ON e.user_account_id = ua.user_account_id";
    $stmt_employees = $pdo->prepare($sql_employees);
    $stmt_employees->execute();
    $employees = $stmt_employees->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching employee data: " . $e->getMessage();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editEmployee'])) {
    $employee_id = isset($_POST["employee_id"]) ? (int) $_POST["employee_id"] : 0;
    $updated_first_name = isset($_POST["first_name"]) ? trim($_POST["first_name"]) : '';
    $updated_middle_name = isset($_POST["middle_name"]) ? trim($_POST["middle_name"]) : '';
    $updated_last_name = isset($_POST["last_name"]) ? trim($_POST["last_name"]) : '';
    $updated_gender = isset($_POST["gender"]) ? trim($_POST["gender"]) : '';
    $updated_mobile = isset($_POST["mobile"]) ? trim($_POST["mobile"]) : '';
    $updated_street = isset($_POST["street"]) ? trim($_POST["street"]) : '';
    $updated_barangay = isset($_POST["barangay"]) ? trim($_POST["barangay"]) : '';
    $updated_city = isset($_POST["city"]) ? trim($_POST["city"]) : '';
    $updated_province = isset($_POST["province"]) ? trim($_POST["province"]) : '';
    $updated_status = isset($_POST["status"]) ? trim($_POST["status"]) : '';
    $updated_email = isset($_POST["email"]) ? trim($_POST["email"]) : '';
    $updated_dob = isset($_POST["dob"]) ? trim($_POST["dob"]) : '';
    $updated_hire_date = isset($_POST["hire_date"]) ? trim($_POST["hire_date"]) : '';
    $updated_civil_status = isset($_POST["civil_status"]) ? trim($_POST["civil_status"]) : '';
    $updated_sss_number = isset($_POST["sss_number"]) ? trim($_POST["sss_number"]) : '';
    $updated_philhealth_number = isset($_POST["philhealth_number"]) ? trim($_POST["philhealth_number"]) : '';
    $updated_pagibig_number = isset($_POST["pagibig_number"]) ? trim($_POST["pagibig_number"]) : '';
    $updated_tin_number = isset($_POST["tin_number"]) ? trim($_POST["tin_number"]) : '';
    $updated_emergency_contact_name = isset($_POST["emergency_contact_name"]) ? trim($_POST["emergency_contact_name"]) : '';
    $updated_emergency_contact_number = isset($_POST["emergency_contact_number"]) ? trim($_POST["emergency_contact_number"]) : '';
    $updated_emergency_contact_relationship = isset($_POST["emergency_contact_relationship"]) ? trim($_POST["emergency_contact_relationship"]) : '';


    if ($employee_id > 0 && !empty($updated_last_name)) {
        try {
            $sql_update = "UPDATE employee 
            SET 
            first_name = :first_name, 
            middle_name = :middle_name,
            last_name = :last_name,
            gender = :gender,
            mobile = :mobile,
            street = :street,
            barangay = :barangay,
            city = :city,
            province = :province,
            status = :status,
            email = :email,
            dob = :dob,
            hire_date = :hire_date,
            civil_status = :civil_status,
            sss_number = :sss_number,
            philhealth_number = :philhealth_number,
            pagibig_number = :pagibig_number,
            tin_number = :tin_number,
            emergency_contact_name = :emergency_contact_name,
            emergency_contact_number = :emergency_contact_number,
            emergency_contact_relationship = :emergency_contact_relationship,
            date_modified = NOW()
            WHERE 
            employee_id = :employee_id";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([
                ':first_name' => $updated_first_name,
                ':middle_name' => $updated_middle_name,
                ':last_name' => $updated_last_name,
                ':gender' => $updated_gender,
                ':mobile' => $updated_mobile,
                ':street' => $updated_street,
                ':barangay' => $updated_barangay,
                ':city' => $updated_city,
                ':province' => $updated_province,
                ':status' => $updated_status,
                ':email' => $updated_email,
                ':dob' => $updated_dob,
                ':hire_date' => $updated_hire_date,
                ':civil_status' => $updated_civil_status,
                ':sss_number' => $updated_sss_number,
                ':philhealth_number' => $updated_philhealth_number,
                ':pagibig_number' => $updated_pagibig_number,
                ':tin_number' => $updated_tin_number,
                ':emergency_contact_name' => $updated_emergency_contact_name,
                ':emergency_contact_number' => $updated_emergency_contact_number,
                ':emergency_contact_relationship' => $updated_emergency_contact_relationship,
                ':employee_id' => $employee_id
            ]);
            header("Location: employees.php");
            exit();
        } catch (PDOException $e) {
            echo "Error updating employee data: " . $e->getMessage();
        }
    } else {
        echo "Invalid input for editing employee.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addEmployee'])) {

    $username = isset($_POST["username"]) ? trim($_POST["username"]) : '';
    $password = isset($_POST["pass"]) ? trim($_POST["pass"]) : '';
    $email = isset($_POST["email"]) ? trim($_POST["email"]) : '';

    if (!empty($username) && !empty($password) && !empty($email)) {
        try {

            // Insert into user_account
            $sql_insert_user = "INSERT INTO user_account (username, pass, email, account_type) 
                                VALUES (:username, :password, :email, :account_type)";
            $stmt_user = $pdo->prepare($sql_insert_user);
            $stmt_user->execute([
                ':username' => $username,
                ':password' => $password,
                ':email' => $email,
                ':account_type' => 'User'
            ]);

            // Retrieve the last inserted user_account_id
            $user_account_id = $pdo->lastInsertId();

            // Gather and sanitize employee details
            $first_name = trim($_POST['first_name'] ?? '');
            $middle_name = trim($_POST['middle_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $gender = trim($_POST['gender'] ?? '');
            $mobile = trim($_POST['mobile'] ?? '');
            $dob = trim($_POST['dob'] ?? '');

            $street = trim($_POST['street'] ?? '');
            $barangay = trim($_POST['barangay'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $province = trim($_POST['province'] ?? '');

            $status = trim($_POST['status'] ?? '');
            $hire_date = trim($_POST['hire_date'] ?? '');
            $civil_status = trim($_POST['civil_status'] ?? '');

            $sss_number = trim($_POST['sss_number'] ?? '');
            $philhealth_number = trim($_POST['philhealth_number'] ?? '');
            $pagibig_number = trim($_POST['pagibig_number'] ?? '');

            $emergency_contact_name = trim($_POST['emergency_contact_name'] ?? '');
            $emergency_contact_number = trim($_POST['emergency_contact_number'] ?? '');
            $emergency_contact_relationship = trim($_POST['emergency_contact_relationship'] ?? '');

            // Insert into employee
            $sql_create_user = "INSERT INTO employee (
                email, first_name, middle_name, last_name, gender, mobile, 
                street, barangay, city, province, status, 
                dob, hire_date, civil_status, sss_number, 
                philhealth_number, pagibig_number, emergency_contact_name, 
                emergency_contact_number, emergency_contact_relationship,
                date_created, user_account_id
            ) VALUES (
                :email, :first_name, :middle_name, :last_name, :gender, :mobile, 
                :street, :barangay, :city, :province, :status, 
                :dob, :hire_date, :civil_status, :sss_number, 
                :philhealth_number, :pagibig_number, :emergency_contact_name, 
                :emergency_contact_number, :emergency_contact_relationship,
                NOW(), :user_account_id
            )";

            $stmt_employee = $pdo->prepare($sql_create_user);
            $stmt_employee->execute([
                ':email' => $email,
                ':first_name' => $first_name,
                ':middle_name' => $middle_name,
                ':last_name' => $last_name,
                ':gender' => $gender,
                ':mobile' => $mobile,
                ':street' => $street,
                ':barangay' => $barangay,
                ':city' => $city,
                ':province' => $province,
                ':status' => $status,
                ':dob' => $dob,
                ':hire_date' => $hire_date,
                ':civil_status' => $civil_status,
                ':sss_number' => $sss_number,
                ':philhealth_number' => $philhealth_number,
                ':pagibig_number' => $pagibig_number,
                ':emergency_contact_name' => $emergency_contact_name,
                ':emergency_contact_number' => $emergency_contact_number,
                ':emergency_contact_relationship' => $emergency_contact_relationship,
                ':user_account_id' => $user_account_id,
            ]);

            header("Location: employees.php");
            exit();
        } catch (PDOException $e) {
            echo "Error inserting data: " . $e->getMessage();
        }
    } else {
        echo "Invalid input for adding employee.";
    }
}

$searchTerm = '';
if (isset($_GET['search'])) {
    $searchTerm = '%' . trim($_GET['search']) . '%';

    try {
        $sql_employees = "SELECT
        e.*,
        j.job_name,
        d.department_name,
        d.branch AS department_branch,
        ua.avatar
    FROM employee e
    LEFT JOIN job j ON e.job_id = j.job_id
    LEFT JOIN department d ON e.department_id = d.department_id
    LEFT JOIN user_account ua ON e.user_account_id = ua.user_account_id
    WHERE e.first_name LIKE :search OR 
          e.last_name LIKE :search OR
          e.email LIKE :search OR
          e.mobile LIKE :search OR
          e.status LIKE :search OR
          e.barangay LIKE :search OR
          e.street LIKE :search OR
          e.city LIKE :search OR
          e.status LIKE :search OR
          e.province LIKE :search OR
          j.job_name LIKE :search OR
          d.department_name LIKE :search";

        $stmt_employees = $pdo->prepare($sql_employees);
        $stmt_employees->execute([':search' => $searchTerm]);
        $employees = $stmt_employees->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error searching employees: " . $e->getMessage();
    }
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
                        <div><a href="payroll.php"><img src="assets/images/icons/payroll-icon.png" alt=""></a>
                        </div>
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
                            <div class="employee-header-div">
                                <form method="GET" action="employees.php">

                                    <input type="text" id="employee_search" type="employee_search" name="search"
                                        placeholder="Search employees..."
                                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                                    <button type="submit" style="margin: 0px">
                                        <img src="assets/images/icons/search-icon.png" alt="Search">
                                    </button>
                                    <div> <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                                            <a href="employees.php" class="clear-search">Clear Search</a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                            <div class="employee-header-div">
                                <div><button class="employee-button" onClick="openAddEmployeeModal()">add
                                        employee</button></div>
                            </div>
                            <!-- <div class="search-container">
                                <form method="GET" action="employees.php">
                                    <div>
                                        <input type="text" name="search" placeholder="Search employees..."
                                            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                                    </div>
                                    <div>
                                        <button type="submit">asdasd
                                            <img class="img-resize" src="assets/images/icons/search-icon.png"
                                                alt="Search">
                                        </button>
                                    </div>
                                </form>
                            </div> -->
                            <!-- <div><button onClick="openAddEmployeeModal()">add employee</button></div> -->
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
                                                class="employee-display-list text-center"
                                                onclick="openModal(<?php echo $employee['employee_id'] ?>)">
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
                                                <td><img class="edit-icon" src="assets/images/icons/edit-icon2.png" alt=""
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
                    <h2 class="text-capitalize">Edit Employee Details</h2>
                </div>
            </div>
            <form action="" method="POST">
                <div id="modal-content" class="employee-detail-container">
                    <div class="employee-detail-container-group font-medium">
                        <div class="employee-detail-fields">
                            <label for="employee_id">Employee ID</label>
                            <input type="text" name="employee_id">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="first_name">First Name</label>
                            <input type="text" name="first_name">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="middle_name">Middle Name</label>
                            <input type="text" name="middle_name">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="last_name">Last Name</label>
                            <input type="text" name="last_name">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="gender">Gender</label>
                            <Select name="gender">
                                <option value="Male" <?php echo isset($employee['gender']) && $employee['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo isset($employee['gender']) && $employee['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo isset($employee['gender']) && $employee['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                            </Select>
                        </div>

                        <div class="employee-detail-fields">
                            <label for="mobile">Mobile</label>
                            <input type="text" name="mobile">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="gender">Street</label>
                            <input type="text" name="street">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="barangay">Brangay</label>
                            <input type="text" name="barangay">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="city">City</label>
                            <input type="text" name="city">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="province">Province</label>
                            <input type="text" name="province">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="status">Status</label>
                            <Select name="status">
                                <option value="Active" <?php echo isset($employee['status']) && $employee['status'] == 'Active' ? 'selected' : ''; ?>>Active</option>
                                <option value="Inactive" <?php echo isset($employee['status']) && $employee['status'] == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                <option value="Terminated" <?php echo isset($employee['status']) && $employee['status'] == 'Terminated' ? 'selected' : ''; ?>>Terminated</option>
                                <option value="Resigned" <?php echo isset($employee['status']) && $employee['status'] == 'Resigned' ? 'selected' : ''; ?>>Resigned</option>
                            </Select>
                        </div>

                        <div class="employee-detail-fields">
                            <label for="email">Email</label>
                            <input type="email" name="email">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="dob">Date of Birth</label>
                            <input type="date" name="dob">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="hire_date">Hire_Date</label>
                            <input type="date" name="hire_date">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="civil_status">Civil Status</label>
                            <Select name="civil_status">
                                <option value="Single" <?php echo isset($employee['civil_status']) && $employee['civil_status'] == 'Single' ? 'selected' : ''; ?>>Single</option>
                                <option value="Married" <?php echo isset($employee['civil_status']) && $employee['civil_status'] == 'Married' ? 'selected' : ''; ?>>Married</option>
                                <option value="Widowed" <?php echo isset($employee['civil_status']) && $employee['civil_status'] == 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                                <option value="Seperated" <?php echo isset($employee['civil_status']) && $employee['civil_status'] == 'Seperated' ? 'selected' : ''; ?>>Seperated</option>
                            </Select>
                        </div>

                        <div class="employee-detail-fields">
                            <label for="sss_number">SSS No.</label>
                            <input type="text" name="sss_number">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="philhealth_number">Philhealth/Malasakit No.</label>
                            <input type="text" name="philhealth_number">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="pagibig_number">Pagibig No.</label>
                            <input type="text" name="pagibig_number">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="emergency_contact_name">Emergency Contact Name</label>
                            <input type="text" name="emergency_contact_name">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="emergency_contact_number">Emergency Contact Number</label>
                            <input type="text" name="emergency_contact_number">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="emergency_contact_relationship">Emergency Contact Relationship</label>
                            <input type="text" name="emergency_contact_relationship">
                        </div>


                    </div>
                    <div class="employee-detail-edit-button-container">

                        <div><button class="employee-detail-edit-button" type="submit" name="editEmployee">Edit Employee
                            </button></div>
                        <!-- <div><button class="employee-detail-edit-button close-button"
                                onclick="closeModal()">Close</button></div> -->
                    </div>
                </div>
            </form>
        </dialog>

        <dialog style="border-radius: 10px; border: 1px solid #D1D1D1; width: 70%;" id="add_employee_modal">
            <div>
                <div style="position: absolute; top: 10px; right: 10px; cursor: pointer;" onclick="closeModal()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18" stroke="#333" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                        <path d="M6 6L18 18" stroke="#333" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-capitalize">Add Employee</h2>
                </div>
            </div>
            <form action="" method="POST">
                <div id="modal-content" class="employee-detail-container">
                    <div class="employee-detail-container-group font-medium">

                        <div class="employee-detail-fields">
                            <label for="username">Username</label>
                            <input type="text" name="username">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="password">Password</label>
                            <input type="password" name="pass">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="email">Email</label>
                            <input type="email" name="email">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="first_name">First Name</label>
                            <input type="text" name="first_name">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="middle_name">Middle Name</label>
                            <input type="text" name="middle_name">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="last_name">Last Name</label>
                            <input type="text" name="last_name">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="gender">Gender</label>
                            <Select name="gender">
                                <option disabled selected> -Select- </option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </Select>
                        </div>

                        <div class="employee-detail-fields">
                            <label for="mobile">Mobile</label>
                            <input type="text" name="mobile">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="gender">Street</label>
                            <input type="text" name="street">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="barangay">Barangay</label>
                            <input type="text" name="barangay">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="city">City</label>
                            <input type="text" name="city">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="province">Province</label>
                            <input type="text" name="province">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="status">Status</label>
                            <Select name="status">
                                <option disabled selected> -Select- </option>

                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Terminated">Terminated</option>
                                <option value="Resigned">Resigned</option>
                            </Select>
                        </div>

                        <div class="employee-detail-fields">
                            <label for="dob">Date of Birth</label>
                            <input type="date" name="dob">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="hire_date">Hire_Date</label>
                            <input type="date" name="hire_date">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="civil_status">Civil Status</label>
                            <Select name="civil_status">
                                <option disabled selected> -Select- </option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Widowed">Widowed</option>
                                <option value="Seperated">Seperated</option>
                            </Select>
                        </div>

                        <div class="employee-detail-fields">
                            <label for="sss_number">SSS No.</label>
                            <input type="text" name="sss_number">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="philhealth_number">Philhealth/Malasakit No.</label>
                            <input type="text" name="philhealth_number">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="pagibig_number">Pagibig No.</label>
                            <input type="text" name="pagibig_number">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="emergency_contact_name">Emergency Contact Name</label>
                            <input type="text" name="emergency_contact_name">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="emergency_contact_number">Emergency Contact Number</label>
                            <input type="text" name="emergency_contact_number">
                        </div>

                        <div class="employee-detail-fields">
                            <label for="emergency_contact_relationship">Emergency Contact Relationship</label>
                            <input type="text" name="emergency_contact_relationship">
                        </div>


                    </div>
                    <div class="employee-detail-edit-button-container">

                        <div><button class="employee-detail-edit-button" type="submit" name="addEmployee">Create
                                Employee
                            </button></div>
                        <!-- <div><button class="employee-detail-edit-button close-button"
                                onclick="closeModal()">Close</button></div> -->
                    </div>
                </div>
            </form>
        </dialog>
    </div>


</body>

<script>
    // Retrieve the modal elements from the DOM
    const modal = document.getElementById('myModal');
    const addEmployeeModal = document.getElementById('add_employee_modal');

    // Logout button handler
    document.getElementById("logout-admin").addEventListener("click", () => {
        window.location.href = "logout.php";
    });

    // (Optional) Log the addEmployeeModal element for debugging
    console.log(addEmployeeModal);

    // Close the add employee modal if clicking outside its content
    addEmployeeModal.addEventListener('click', (e) => {
        if (e.target === addEmployeeModal) {
            closeAddEmployeeModal();
        }
    });

    // Function to open the add employee modal
    function openAddEmployeeModal() {
        addEmployeeModal.showModal();
    }

    // Close the main modal if clicking outside its content
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });

    // Function to open the main modal and populate its fields based on row data
    function openModal(id) {
        const currentRow = document.querySelector(`#row-${id}`);
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

        // Populate input fields with data
        Object.keys(data).forEach(key => {
            const inputElement = document.querySelector(`input[name="${key}"]`);
            if (inputElement) {
                inputElement.value = data[key];
            }
        });

        // Populate select elements if available
        const statusSelect = document.querySelector(`select[name="status"]`);
        if (statusSelect && data.status) {
            statusSelect.value = data.status;
        }

        const civilStatusSelect = document.querySelector(`select[name="civil_status"]`);
        if (civilStatusSelect && data.civil_status) {
            civilStatusSelect.value = data.civil_status;
        }

        const genderSelect = document.querySelector(`select[name="gender"]`);
        if (genderSelect && data.gender) {
            genderSelect.value = data.gender;
        }

        modal.showModal();
    }

    // Function to close the main modal
    function closeModal() {
        modal.close();
    }

    // Function to close the add employee modal
    function closeAddEmployeeModal() {
        addEmployeeModal.close();
    }
    document.getElementById('employee_search').addEventListener('input', function () {
        document.getElementById('employee_search').submit();
    });

    function searchEmployees() {
        const input = document.getElementById('employeeSearch');
        const filter = input.value.toUpperCase();
        const table = document.querySelector('.employee-display table');
        const rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) { // Start from 1 to skip header row
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