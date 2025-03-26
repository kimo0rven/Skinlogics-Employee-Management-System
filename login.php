<?php
session_start();
include("includes/database.php");

echo $_POST["submit"];
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

            foreach ($user as $key => $value) {
                $_SESSION[$key] = $value;
            }
            $_SESSION['loggedin'] = true;

            header("Location: dashboard.php");
            exit();

        } else {
            echo "Invalid email or password.";
            exit();
        }

    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }
}
?>