<?php
session_start();

if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

$user_account_id = (int) $_SESSION["user_account_id"];

include 'includes/database.php';
include 'config.php';

$employees = [];
try {
    $sql_employees = "SELECT
        e.*,
        j.job_name,
        d.department_name,
        d.department_id,
        d.branch AS department_branch,
        ua.avatar,
        ua.role_id,
        r.role_name
    FROM employee e
    LEFT JOIN job j ON e.job_id = j.job_id
    LEFT JOIN department d ON j.department_id = d.department_id
    LEFT JOIN user_account ua ON e.user_account_id = ua.user_account_id
    LEFT JOIN roles r ON ua.role_id = r.role_id;";

    $stmt_employees = $pdo->prepare($sql_employees);
    $stmt_employees->execute();
    $employees = $stmt_employees->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching employee data: " . $e->getMessage();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editEmployee'])) {
    $employee_id = isset($_POST["employee_id"]) ? (int) $_POST["employee_id"] : 0;
    $fields = [
        "employee_id",
        "first_name",
        "middle_name",
        "last_name",
        "gender",
        "mobile",
        "street",
        "barangay",
        "city",
        "province",
        "status",
        "email",
        "dob",
        "hire_date",
        "civil_status",
        "sss_number",
        "philhealth_number",
        "pagibig_number",
        "tin_number",
        "emergency_contact_name",
        "emergency_contact_number",
        "emergency_contact_relationship",
        "job_id",
        "role_id"
    ];

    $formattedUpdate = [];
    foreach ($fields as $field) {
        $formattedUpdate["$field"] = isset($_POST[$field]) ? trim($_POST[$field]) : ($field === "employee_id" ? 0 : '');
    }
    extract($formattedUpdate);
    echo 1;
    echo "formatted: ";
    print_r($formattedUpdate);

    if ($formattedUpdate['employee_id'] > 0 && !empty($formattedUpdate['last_name'])) {
        try {
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
                    job_id = :job_id,
                    date_modified = NOW()
                WHERE 
                    employee_id = :employee_id";

            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([
                ':first_name' => $formattedUpdate['first_name'],
                ':middle_name' => $formattedUpdate['middle_name'],
                ':last_name' => $formattedUpdate['last_name'],
                ':gender' => $formattedUpdate['gender'],
                ':mobile' => $formattedUpdate['mobile'],
                ':street' => $formattedUpdate['street'],
                ':barangay' => $formattedUpdate['barangay'],
                ':city' => $formattedUpdate['city'],
                ':province' => $formattedUpdate['province'],
                ':status' => $formattedUpdate['status'],
                ':email' => $formattedUpdate['email'],
                ':dob' => $formattedUpdate['dob'],
                ':hire_date' => $formattedUpdate['hire_date'],
                ':civil_status' => $formattedUpdate['civil_status'],
                ':sss_number' => $formattedUpdate['sss_number'],
                ':philhealth_number' => $formattedUpdate['philhealth_number'],
                ':pagibig_number' => $formattedUpdate['pagibig_number'],
                ':tin_number' => $formattedUpdate['tin_number'],
                ':emergency_contact_name' => $formattedUpdate['emergency_contact_name'],
                ':emergency_contact_number' => $formattedUpdate['emergency_contact_number'],
                ':emergency_contact_relationship' => $formattedUpdate['emergency_contact_relationship'],
                ':job_id' => $formattedUpdate['job_id'],
                ':employee_id' => $formattedUpdate['employee_id']
            ]);

            $sql = 'SELECT user_account_id FROM employee WHERE employee_id = :employee_id';
            $employeeStmt = $pdo->prepare($sql);
            $employeeStmt->execute([
                ':employee_id' => $formattedUpdate['employee_id']
            ]);
            $user_account_id = $employeeStmt->fetchColumn();

            if (!$user_account_id) {
                throw new Exception("No user_account_id found for employee_id: " . $formattedUpdate['employee_id']);
            }

            $sql_current_role = "SELECT role_id FROM user_account WHERE user_account_id = :user_account_id";
            $stmt_current = $pdo->prepare($sql_current_role);
            $stmt_current->execute([':user_account_id' => $user_account_id]);
            $currentRole = $stmt_current->fetchColumn();

            if ($currentRole != $formattedUpdate['role_id']) {
                $sql_update_role = "UPDATE user_account 
                    SET role_id = :role_id 
                    WHERE user_account_id = :user_account_id";
                $stmt_update_role = $pdo->prepare($sql_update_role);
                $stmt_update_role->execute([
                    ':role_id' => $formattedUpdate['role_id'],
                    ':user_account_id' => $user_account_id
                ]);

                if ($stmt_update_role->rowCount() == 0) {
                    throw new Exception("User account update did not affect any rows. Check user_account_id and role_id.");
                }
            }

            header("Location: employees.php");
            exit();

        } catch (PDOException $e) {
            echo "Error updating employee data: " . $e->getMessage();
        } catch (Exception $ex) {
            echo "General error: " . $ex->getMessage();
        }
    } else {
        echo "Invalid input for editing employee.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addEmployee'])) {

    $username = isset($_POST["username"]) ? trim($_POST["username"]) : '';
    $password = isset($_POST["password"]) ? trim($_POST["password"]) : '';
    $email = isset($_POST["email"]) ? trim($_POST["email"]) : '';
    $role_id = trim($_POST['role_id'] ?? '');

    print_r($_POST);
    if (!empty($username) && !empty($password) && !empty($email)) {
        try {
            $sql_insert_user = "INSERT INTO user_account (username, pass, email, role_id) 
                                VALUES (:username, :password, :email, :role_id)";
            $stmt_user = $pdo->prepare($sql_insert_user);
            $stmt_user->execute([
                ':username' => $username,
                ':password' => $password,
                ':email' => $email,
                ':role_id' => $role_id
            ]);

            print_r($stmt_user);

            $user_account_id = $pdo->lastInsertId();

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
                ':user_account_id' => $user_account_id
            ]);
            print_r($stmt_employee);

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
                            d.department_id,
                            d.branch AS department_branch,
                            ua.avatar
                        FROM employee e
                        LEFT JOIN job j ON e.job_id = j.job_id
                        LEFT JOIN department d ON j.department_id = d.department_id
                        LEFT JOIN user_account ua ON e.user_account_id = ua.user_account_id
                        WHERE e.first_name LIKE :search OR 
                            e.last_name LIKE :search OR
                            e.email LIKE :search OR
                            e.mobile LIKE :search OR
                            e.status LIKE :search OR
                            e.barangay LIKE :search OR
                            e.street LIKE :search OR
                            e.city LIKE :search OR
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

try {
    $sql = "SELECT * FROM department";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("" . $e->getMessage());
}

try {
    $sql = "SELECT * FROM job";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("" . $e->getMessage());
}

try {
    $sql = "SELECT * FROM roles";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                            <h1>EMPLOYEES</h1>
                        </div>
                        <div id="logout-admin" class="dashboard-content-header font-medium">
                            <?php include('includes/header-avatar.php') ?>
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
                                        <img class="img-resize" height="32px" width="32px"
                                            src="assets/images/icons/search-icon.png" alt="Search">
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
                        </div>

                        <div class="employee-display" style="overflow: auto;">
                            <table class="employee-table">
                                <thead class="font-bold">
                                    <tr>
                                        <th>Avatar</th>
                                        <th>Name</th>
                                        <th>Job Title</th>
                                        <th>Department</th>
                                        <th>Branch</th>
                                        <th>Role</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>Status</th>
                                        <th>Edit</th>
                                    </tr>
                                </thead>
                                <tbody class="font-medium">
                                    <?php if (!empty($employees)): ?>
                                        <?php foreach ($employees as $employee): ?>
                                            <tr id="row-<?php echo $employee['employee_id'] ?>" style="white-space: nowrap;"
                                                data-user='<?php echo json_encode($employee); ?>'
                                                class="employee-display-list text-center"
                                                onclick="openModal(<?php echo $employee['employee_id'] ?>)">
                                                <td style="text-align: center;">
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
                                                <td><?php echo htmlspecialchars($employee['role_name']); ?></td>
                                                <td>
                                                    <p><a id="mailto"
                                                            href="mailto:<?php echo htmlspecialchars($employee['email']); ?>"><?php echo htmlspecialchars($employee['email']); ?></a>
                                                    </p>
                                                </td>

                                                <td><?php echo htmlspecialchars($employee['mobile']); ?></td>
                                                <!-- <td><?php echo htmlspecialchars($employee['status']); ?></td> -->
                                                <td>
                                                    <?php
                                                    $status = htmlspecialchars($employee['status']); // Get the status from the PHP variable
                                            
                                                    switch ($status) {
                                                        case 'Active':
                                                            echo "<div class='employee-status employee-status-active'>Active</div>";
                                                            break;
                                                        case 'Inactive':
                                                            echo "<div class='employee-status employee-status-inactive'>Inactive</div>";
                                                            break;
                                                        case 'Resigned':
                                                            echo "<div class='employee-status employee-status-resigned'>Resigned</div>";
                                                            break;
                                                        case 'Terminated':
                                                            echo "<div class='employee-status employee-status-terminated'>Terminated</div>";
                                                            break;
                                                        default:
                                                            echo "<div>Unknown Status</div>";
                                                            break;
                                                    }
                                                    ?>
                                                </td>

                                                <td style="text-align: center;"><img class="edit-icon"
                                                        src="assets/images/icons/edit-icon2.png" alt=""
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

        <dialog style="border-radius: 10px; border: 1px solid #D1D1D1;" id="myModal">
            <form action="" method="POST" class="flex flex-column gap-20">
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
                        <p class="text-capitalize font-bold" style="font-size: 32px;margin: 0">Edit Employee Details</p>
                    </div>
                </div>
                <div id="modal-content" class="employee-detail-container">
                    <div class="employee-detail-container-group gap-20 font-medium">
                        <div clas="flex flex-row gap-20">
                            <div class="flex flex-row gap-20">
                                <div class="employee-detail-fields">
                                    <label for="employee_id">Employee ID</label>
                                    <input type="text" name="employee_id" readonly>
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
                            </div>

                            <div class="flex flex-row gap-20">
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
                            </div>

                            <div class="flex flex-row gap-20 space-between">
                                <div class="employee-detail-fields">
                                    <label for="mobile">Mobile</label>
                                    <input type="text" name="mobile">
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
                                    <label for="civil_status">Civil Status</label>
                                    <Select name="civil_status">
                                        <option value="Single" <?php echo isset($employee['civil_status']) && $employee['civil_status'] == 'Single' ? 'selected' : ''; ?>>Single</option>
                                        <option value="Married" <?php echo isset($employee['civil_status']) && $employee['civil_status'] == 'Married' ? 'selected' : ''; ?>>Married</option>
                                        <option value="Widowed" <?php echo isset($employee['civil_status']) && $employee['civil_status'] == 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                                        <option value="Seperated" <?php echo isset($employee['civil_status']) && $employee['civil_status'] == 'Seperated' ? 'selected' : ''; ?>>Seperated
                                        </option>
                                    </Select>
                                </div>

                                <div class="employee-detail-fields">
                                    <label for="gender">Gender</label>
                                    <Select name="gender">
                                        <option value="Male" <?php echo isset($employee['gender']) && $employee['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo isset($employee['gender']) && $employee['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo isset($employee['gender']) && $employee['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </Select>
                                </div>
                            </div>

                            <div class="flex flex-row gap-20 flex-start">

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
                                    <label for="hire_date">Hire Date</label>
                                    <input type="date" name="hire_date">
                                </div>

                            </div>
                        </div>
                        <div class="vl">
                        </div>
                        <div clas="flex flex-row">

                            <div class="flex flex-row gap-20 space-between">
                                <div class="employee-detail-fields">
                                    <label for="sss_number">SSS No.</label>
                                    <input type="text" name="sss_number">
                                </div>

                                <div class="employee-detail-fields">
                                    <label for="philhealth_number">Philhealth/Malasakit No.</label>
                                    <input type="text" name="philhealth_number">
                                </div>

                                <div class="employee-detail-fields">
                                    <label for="tin_number">Tin Number</label>
                                    <input type="text" name="tin_number">
                                </div>

                                <div class="employee-detail-fields">
                                    <label for="pagibig_number">Pagibig No.</label>
                                    <input type="text" name="pagibig_number">
                                </div>
                            </div>

                            <div class="flex flex-row gap-20 flex-start">
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

                            <div class="flex flex-row gap-20">
                                <div class="employee-detail-fields">
                                    <label for="job_id" style="display:block; margin-bottom: 5px;">Job</label>
                                    <select name="job_id" id="job_id">
                                        <?php
                                        foreach ($jobs as $job) {
                                            echo '<option value="' . htmlspecialchars($job['job_id']) . '">' . htmlspecialchars($job['job_name']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="employee-detail-fields">
                                    <label for="department_id"
                                        style="display:block; margin-bottom: 5px;">Department</label>
                                    <select name="department_id" id="department_id">
                                        <?php
                                        foreach ($departments as $department) {
                                            echo '<option value="' . htmlspecialchars($department['department_id']) . '">' . htmlspecialchars($department['department_name']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="flex flex-row gap-20">
                                <div class="employee-detail-fields">
                                    <label for="manager_id" style="display:block; margin-bottom: 5px;">Manager</label>
                                    <select name="manager_id" id="manager_id" style="pointer-events: none;">
                                        <option value="0">No Manager</option>
                                        <?php
                                        foreach ($employees as $employee) {
                                            echo '<option value="' . htmlspecialchars($employee['employee_id']) . '">' . htmlspecialchars($employee['first_name'] . " " . $employee['last_name']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="employee-detail-fields">
                                    <label for="team_leader_id" style="display:block; margin-bottom: 5px;">Team
                                        Leader</label>
                                    <select name="team_leader_id" id="team_leader_id" style="pointer-events: none;">
                                        <option value="0">No Team Leader</option>

                                        <?php
                                        foreach ($employees as $employee) {
                                            echo '<option value="' . htmlspecialchars($employee['employee_id']) . '">' . htmlspecialchars($employee['first_name'] . " " . $employee['last_name']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="employee-detail-fields">
                                    <label for="role_id" style="display:block; margin-bottom: 5px;">Acount
                                        Role</label>
                                    <select name="role_id" id="role_id">
                                        <option>No Role</option>
                                        <?php
                                        foreach ($roles as $role) {
                                            echo '<option value="' . htmlspecialchars($role['role_id']) . '">' . htmlspecialchars($role['role_name']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                        </div>

                    </div>


                </div>
                <div class="employee-detail-edit-button-container">

                    <div><button class="employee-detail-edit-button" type="submit" name="editEmployee">Edit Employee
                        </button></div>
                </div>
            </form>
        </dialog>

        <dialog style="border-radius: 10px; border: 1px solid #D1D1D1;" id="add_employee_modal">
            <form action="" method="POST" class="flex flex-column gap-20">
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
                        <p class="text-capitalize font-bold" style="font-size: 32px;margin: 0">New Employee</p>
                    </div>
                </div>
                <div id="modal-content" class="employee-detail-container">
                    <div class="employee-detail-container-group gap-20 font-medium">
                        <div clas="flex flex-row gap-20">
                            <div class="flex flex-row gap-20">

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
                            </div>

                            <div class="flex flex-row gap-20">
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
                            </div>

                            <div class="flex flex-row gap-20 space-between">
                                <div class="employee-detail-fields">
                                    <label for="mobile">Mobile</label>
                                    <input type="text" name="mobile">
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
                                    <label for="civil_status">Civil Status</label>
                                    <Select name="civil_status">
                                        <option value="Single" <?php echo isset($employee['civil_status']) && $employee['civil_status'] == 'Single' ? 'selected' : ''; ?>>Single</option>
                                        <option value="Married" <?php echo isset($employee['civil_status']) && $employee['civil_status'] == 'Married' ? 'selected' : ''; ?>>Married</option>
                                        <option value="Widowed" <?php echo isset($employee['civil_status']) && $employee['civil_status'] == 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                                        <option value="Seperated" <?php echo isset($employee['civil_status']) && $employee['civil_status'] == 'Seperated' ? 'selected' : ''; ?>>Seperated
                                        </option>
                                    </Select>
                                </div>

                                <div class="employee-detail-fields">
                                    <label for="gender">Gender</label>
                                    <Select name="gender">
                                        <option value="Male" <?php echo isset($employee['gender']) && $employee['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo isset($employee['gender']) && $employee['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo isset($employee['gender']) && $employee['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </Select>
                                </div>
                            </div>

                            <div class="flex flex-row gap-20 flex-start">

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
                                    <label for="hire_date">Hire Date</label>
                                    <input type="date" name="hire_date">
                                </div>

                                <div class="employee-detail-fields">
                                    <label for="username">Username</label>
                                    <input type="text" name="username">
                                </div>

                                <div class="employee-detail-fields">
                                    <label for="password">Password</label>
                                    <input type="password" name="password">
                                </div>

                            </div>
                        </div>
                        <div class="vl">
                        </div>
                        <div clas="flex flex-row">

                            <div class="flex flex-row gap-20 space-between">
                                <div class="employee-detail-fields">
                                    <label for="sss_number">SSS No.</label>
                                    <input type="text" name="sss_number">
                                </div>

                                <div class="employee-detail-fields">
                                    <label for="philhealth_number">Philhealth/Malasakit No.</label>
                                    <input type="text" name="philhealth_number">
                                </div>

                                <div class="employee-detail-fields">
                                    <label for="tin_number">Tin Number</label>
                                    <input type="text" name="tin_number">
                                </div>

                                <div class="employee-detail-fields">
                                    <label for="pagibig_number">Pagibig No.</label>
                                    <input type="text" name="pagibig_number">
                                </div>
                            </div>

                            <div class="flex flex-row gap-20 flex-start">
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

                            <div class="flex flex-row gap-20">
                                <div class="employee-detail-fields">
                                    <label for="job_id" style="display:block; margin-bottom: 5px;">Job</label>
                                    <select name="job_id" id="job_id">
                                        <?php
                                        foreach ($jobs as $job) {
                                            echo '<option value="' . htmlspecialchars($job['job_id']) . '">' . htmlspecialchars($job['job_name']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="employee-detail-fields">
                                    <label for="department_id"
                                        style="display:block; margin-bottom: 5px;">Department</label>
                                    <select name="department_id" id="department_id">
                                        <?php
                                        foreach ($departments as $department) {
                                            echo '<option value="' . htmlspecialchars($department['department_id']) . '">' . htmlspecialchars($department['department_name']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="flex flex-row gap-20">
                                <div class="employee-detail-fields">
                                    <label for="manager_id" style="display:block; margin-bottom: 5px;">Manager</label>
                                    <select name="manager_id" id="manager_id">
                                        <option value="0">No Manager</option>
                                        <?php
                                        foreach ($employees as $employee) {
                                            echo '<option value="' . htmlspecialchars($employee['employee_id']) . '">' . htmlspecialchars($employee['first_name'] . " " . $employee['last_name']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="employee-detail-fields">
                                    <label for="team_leader_id" style="display:block; margin-bottom: 5px;">Team
                                        Leader</label>
                                    <select name="team_leader_id" id="team_leader_id">
                                        <option value="0">No Team Leader</option>

                                        <?php
                                        foreach ($employees as $employee) {
                                            echo '<option value="' . htmlspecialchars($employee['employee_id']) . '">' . htmlspecialchars($employee['first_name'] . " " . $employee['last_name']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="employee-detail-fields">
                                    <label for="role_id" style="display:block; margin-bottom: 5px;">Acount
                                        Role</label>
                                    <select name="role_id" id="role_id">
                                        <?php
                                        foreach ($roles as $role) {
                                            echo '<option value="' . htmlspecialchars($role['role_id']) . '">' . htmlspecialchars($role['role_name']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                        </div>

                    </div>


                </div>
                <div class="employee-detail-edit-button-container">

                    <div><button class="employee-detail-edit-button" type="submit" name="addEmployee">Add Employee
                        </button></div>
                </div>
            </form>
        </dialog>
    </div>


</body>

<script>
    const modal = document.getElementById('myModal');
    const addEmployeeModal = document.getElementById('add_employee_modal');

    addEmployeeModal.addEventListener('click', (e) => {
        if (e.target === addEmployeeModal) {
            closeAddEmployeeModal();
        }
    });

    function openAddEmployeeModal() {
        addEmployeeModal.showModal();
    }

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });

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

        const jobSelect = document.querySelector(`select[name="job_id"]`);
        if (jobSelect && data.job_name) {
            jobSelect.value = data.job_id;
        }

        const departmentSelect = document.querySelector(`select[name="department_id"]`);
        if (departmentSelect && data.department_name) {
            departmentSelect.value = data.department_id;
        }

        const managerSelect = document.querySelector('select[name="manager_id"]');
        if (managerSelect) {
            if (data.manager_id === null || data.manager_id === 'null') {
                data.manager_id = 0;
            }
            managerSelect.value = data.manager_id;
        }

        const teamLeaderSelect = document.querySelector('select[name="team_leader_id"]');
        if (teamLeaderSelect) {
            if (data.team_leader_id === null || data.team_leader_id === 'null') {
                data.team_leader_id = 0;
            }
            teamLeaderSelect.value = data.team_leader_id;
        }

        const roleIdSelect = document.querySelector('select[name="role_id"]');
        if (roleIdSelect && data.role_id) {
            roleIdSelect.value = data.role_id;
        }

        modal.showModal();
    }

    function closeModal() {
        modal.close();
    }

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