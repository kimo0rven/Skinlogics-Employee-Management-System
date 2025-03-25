<?php
session_start();
include("js_php/database.php");

if (isset($_POST["submit"])) {
    $email = $_POST["email"];
    $pass = $_POST["password"];

    try {
        $sql = "SELECT * FROM employee WHERE email = :email AND pass = :pass";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':pass', $pass);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);


        if ($user && $pass === $user['pass']) {

            foreach ($user as $key => $value) {
                $_SESSION[$key] = $value;
            }

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