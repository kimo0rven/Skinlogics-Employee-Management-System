<?php
session_start();
include('includes/database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 🔍 1. DEBUG: Display all POST data
    echo "<h3>Form Data Received:</h3><pre>";
    print_r($_POST);
    echo "</pre>";

    // Collect form data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Always hash!
    $accountType = 'User';

    $first_name = $_POST['firstName'];
    $last_name = $_POST['lastName'];
    $birth_date = $_POST['year'] . '-' . $_POST['month'] . '-' . $_POST['day'];
    $mobile = $_POST['mobile'];
    $gender = $_POST['gender'];

    try {
        // 🔍 2. Insert into user_account table
        $sql = "INSERT INTO user_account (username, email, pass, account_type)
                VALUES (:username, :email, :password, :accountType)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':accountType', $accountType);
        $stmt->execute();

        // 🔍 3. DEBUG: Check insertion result
        echo "<h3>User Account Insertion:</h3><pre>";
        print_r($stmt->rowCount());
        echo "</pre>";

        if ($stmt->rowCount() > 0) {
            echo "<p style='color:green;'>✅ User account created successfully!</p>";
            $lastInsertId = $pdo->lastInsertId();

            // 🔍 4. DEBUG: Display the last inserted user_account ID
            echo "<h3>Last Inserted ID:</h3><pre>";
            print_r($lastInsertId);
            echo "</pre>";

            $_SESSION['employee_id'] = $lastInsertId;

            // 🔍 5. Insert into employee table
            $sql2 = "INSERT INTO employee 
                     (user_account_id, first_name, last_name, dob, email, mobile, gender)
                     VALUES (:user_account_id, :first_name, :last_name, :dob, :email, :mobile, :gender)";
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->bindParam(':user_account_id', $lastInsertId);
            $stmt2->bindParam(':first_name', $first_name);
            $stmt2->bindParam(':last_name', $last_name);
            $stmt2->bindParam(':dob', $birth_date);
            $stmt2->bindParam(':email', $email);
            $stmt2->bindParam(':mobile', $mobile);
            $stmt2->bindParam(':gender', $gender);
            $stmt2->execute();

            // 🔍 6. DEBUG: Confirm employee insertion
            echo "<h3>Employee Table Insertion:</h3><pre>";
            print_r($stmt2->rowCount());
            echo "</pre>";
        } else {
            echo "<p style='color:red;'>❌ Failed to create user account.</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color:red;'>PDO Error:</p><pre>" . $e->getMessage() . "</pre>";
    }
}
?>
