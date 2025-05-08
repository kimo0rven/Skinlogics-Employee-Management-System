<?php
session_start();
include 'includes/database.php';

// Initialize defaults.
$_SESSION['isTeamLeader'] = false;
$_SESSION['isHRManager'] = false;

function redirectUser($setup, $dashboardUrl = 'dashboard.php', $setupUrl = 'setup.php')
{
    header("Location: " . ($setup == 1 ? $dashboardUrl : $setupUrl));
    exit();
}

function getEmployeeDetails(PDO $pdo, int $userAccountId): ?array
{
    $stmt = $pdo->prepare("SELECT setup, employee_id, manager_id, team_leader_id FROM employee WHERE user_account_id = :user_account_id");
    $stmt->bindParam(":user_account_id", $userAccountId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function setEmployeeSession(array $employee, int $roleId)
{
    $_SESSION['employee_id'] = $employee['employee_id'];
    $_SESSION['isTeamLeader'] = $employee['team_leader_id'] == $employee['employee_id'];
    $_SESSION['isHRManager'] = ($employee['manager_id'] == $employee['employee_id'] && $roleId === 2);

    if ($employee['team_leader_id'] == $employee['employee_id']) {
        echo 'true tl';
    } else {
        echo 'not tl';
    }
    echo '<br>';
    if ($employee['manager_id'] == $employee['employee_id'] && $roleId === 2) {
        echo 'true hr manager';
    } else {
        echo 'not hr manager';
    }
    echo '<br>';
    print_r($employee);
    echo '<br>';
    print_r($_SESSION);
}

if (isset($_SESSION['user_account_id'], $_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    try {
        if ($employee = getEmployeeDetails($pdo, $_SESSION['user_account_id'])) {

            setEmployeeSession($employee, $_SESSION['role_id'] ?? 0);
            redirectUser($employee['setup']);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        die("An error occurred. Please try again later.");
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['pass'] ?? '');

    if (empty($username) || empty($password)) {
        die("Username and password cannot be empty.");
    }

    try {
        $stmt = $pdo->prepare("SELECT ua.*, e.* 
                               FROM user_account AS ua
                               INNER JOIN employee AS e ON ua.user_account_id = e.user_account_id
                               WHERE ua.username = :username AND e.status = 'Active' 
                               LIMIT 1");
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

            if ($employee = getEmployeeDetails($pdo, $user['user_account_id'])) {
                setEmployeeSession($employee, $user['role_id']);
                redirectUser($employee['setup']);
                print_r($employee);
            }
        } else {
            die("Invalid username or password.");
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        die("An error occurred. Please try again later.");
    }

    print_r($_SESSION);

}
?>