<?php
session_start();
include("includes/database.php");

if (isset($_SESSION["user_account_id"], $_SESSION['username'])) {
    $_SESSION['loggedin'] = true;

    try {
        $sql = "SELECT setup,employee_id FROM employee WHERE user_account_id = :user_account_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":user_account_id", $_SESSION['user_account_id']);
        $stmt->execute();
        $setup = $stmt->fetch(PDO::FETCH_ASSOC);

        echo "Value of \$setup: ";
        var_dump($setup);
        echo "<br>";

        $_SESSION['employee_id'] = $setup['employee_id'];
        echo $_SESSION['employee_id'];

        if ($setup['setup'] == 1) {
            header("Location: dashboard.php");
        } else {
            header("Location: setup.php");
        }
        exit();
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
        exit();
    }
}

if (isset($_POST["submit"])) {
    $username = $_POST["username"];
    $pass = $_POST["pass"];

    try {
        $sql = "SELECT * FROM user_account WHERE username = :username AND pass = :pass";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':pass', $pass);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $pass === $user['pass']) {
            $_SESSION = $user;
            $_SESSION['loggedin'] = true;

            $sql = "SELECT setup,employee_id FROM employee WHERE user_account_id = :user_account_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":user_account_id", $user['user_account_id']);
            $stmt->execute();
            $setup = $stmt->fetch(PDO::FETCH_ASSOC);

            $_SESSION['employee_id'] = $setup['employee_id'];
            echo $_SESSION['employee_id'];

            if ($setup['setup'] == 1) {
                header("Location: dashboard.php");
            } else {
                header("Location: setup.php");
            }
            exit();
        } else {
            echo "Invalid username or password.";
            exit();
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
        exit();
    }
}
?>