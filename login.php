<?php
session_start();
include 'includes/database.php';


$_SESSION['isTeamLeader'] = false;
$_SESSION['isHRManager'] = false;

function redirectUser(int $setup, string $dashboardUrl = 'dashboard.php', string $setupUrl = 'setup.php'): void
{
    header("Location: " . ($setup === 1 ? $dashboardUrl : $setupUrl));
    exit();
}

function getEmployeeDetails(PDO $pdo, int $userAccountId): ?array
{
    $sql = "SELECT 
                e.employee_id, 
                e.setup, 
                j.job_id, 
                j.department_id AS job_department_id, 
                d.team_leader_id, 
                d.manager_id, 
                d.department_id AS dept_department_id
            FROM employee AS e
            INNER JOIN job AS j ON e.job_id = j.job_id
            INNER JOIN department AS d ON j.department_id = d.department_id
            WHERE e.user_account_id = :user_account_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":user_account_id", $userAccountId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function setEmployeeSession(array $employee, int $roleId): void
{
    $_SESSION['employee_id'] = $employee['employee_id'];
    $_SESSION['isTeamLeader'] = ($employee['team_leader_id'] == $employee['employee_id']);
    $_SESSION['isHRManager'] = ($employee['manager_id'] == $employee['employee_id'] && $roleId === 2);
}

if (!empty($_SESSION['user_account_id']) && !empty($_SESSION['loggedin'])) {
    try {
        if ($employee = getEmployeeDetails($pdo, $_SESSION['user_account_id'])) {
            setEmployeeSession($employee, $_SESSION['role_id'] ?? 0);
            redirectUser((int) $employee['setup']);
        }
        die("Employee record not found.");
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        die("An error occurred. Please try again later.");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['pass'] ?? '');

    if (!$username || !$password) {
        die("Username and password cannot be empty.");
    }

    try {
        $sql = "SELECT ua.*, e.* 
                FROM user_account AS ua
                INNER JOIN employee AS e ON ua.user_account_id = e.user_account_id
                WHERE ua.username = :username AND e.status = 'Active' 
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $password === $user['pass']) {
            $_SESSION = array_merge($_SESSION, [
                'user_account_id' => $user['user_account_id'],
                'username' => $user['username'],
                'role_id' => $user['role_id'],
                'avatar' => $user['avatar'],
                'loggedin' => true
            ]);
            echo $user['user_account_id'];
            print_r($pdo);
            if ($employee = getEmployeeDetails($pdo, $user['user_account_id'])) {
                // print_r($employee);
                setEmployeeSession($employee, $user['role_id']);
                redirectUser((int) $employee['setup']);
            }
            die("Employee record not found.");
        }
        die("Invalid username or password.");

    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        die("An error occurred. Please try again later.");
    }
}
?>