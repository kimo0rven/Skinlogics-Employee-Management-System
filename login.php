<?php
session_start();

include 'includes/database.php';

$_SESSION['isTeamLeader'] = false;
$_SESSION['isHRManager'] = false;

function redirectUser($setup, $dashboardUrl = 'dashboard.php', $setupUrl = 'setup.php')
{
    header("Location: " . ($setup == 1 ? $dashboardUrl : $setupUrl));
    exit();
}

function getEmployeeDetails(PDO $pdo, $userAccountId)
{
    $sql = "SELECT setup, employee_id, manager_id, team_leader_id 
            FROM employee 
            WHERE user_account_id = :user_account_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":user_account_id", $userAccountId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

if (isset($_SESSION['user_account_id'], $_SESSION['username'], $_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    try {
        $employee = getEmployeeDetails($pdo, $_SESSION['user_account_id']);
        if ($employee) {
            $_SESSION['employee_id'] = $employee['employee_id'];

            if ($employee['team_leader_id'] == $employee['employee_id']) {
                $_SESSION['isTeamLeader'] = true;
            }
            if ($employee['manager_id'] == $employee['employee_id'] && !empty($_SESSION['role_id'])) {
                $_SESSION['isHRManager'] = true;
            }

            redirectUser($employee['setup']);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        die("An error occurred. Please try again later.");
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['pass']) ? trim($_POST['pass']) : '';

    if (empty($username) || empty($password)) {
        die("Username and password cannot be empty.");
    }

    try {
        $sql = "SELECT ua.*, e.* 
                FROM user_account AS ua
                INNER JOIN employee AS e ON ua.user_account_id = e.user_account_id
                WHERE ua.username = :username 
                  AND e.status = 'Active'
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $password === $user['pass']) {
            $_SESSION['user_account_id'] = $user['user_account_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['avatar'] = $user['avatar'];
            $_SESSION['loggedin'] = true;

            $employee = getEmployeeDetails($pdo, $user['user_account_id']);
            if ($employee) {
                $_SESSION['employee_id'] = $employee['employee_id'];

                if ($employee['team_leader_id'] == $employee['employee_id']) {
                    $_SESSION['isTeamLeader'] = true;
                }
                if ($employee['manager_id'] == $employee['employee_id'] && ($user['role_id']) == 2) {
                    $_SESSION['isHRManager'] = true;
                }

                redirectUser($employee['setup']);
            }
        } else {
            die("Invalid username or password.");
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        die("An error occurred. Please try again later.");
    }
    exit();
}
?>