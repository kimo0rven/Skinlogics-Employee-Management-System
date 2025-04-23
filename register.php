<?php
include('includes/database.php');
if (isset($_POST['registration'])) {
    print_r($_POST);

    $first_name = $_POST['firstName'];
    $last_name = $_POST['lastName'];
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $birth_date = $_POST['year'] . "-" . $_POST['month'] . "-" . $_POST['day'];
    $gender = $_POST['gender'];
    $mobile = $_POST['mobile'];
    $username = $_POST['username'];
    $accountType = 'User';

    $sql = "INSERT INTO user_account (username, email, pass, account_type) VALUES (:username, :email, :password, :accountType)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':accountType', $accountType);

        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $accountType = 'User';

        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo "User account created successfully!";
            $lastInsertId = $pdo->lastInsertId();
            $_SESSION['employee_id'] = $lastInsertId;

            $sql2 = "INSERT INTO employee (user_account_id, first_name, last_name, dob, email, mobile, gender) VALUES (:user_account_id, :first_name, :last_name, :dob, :email, :mobile, :gender)";
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->bindParam(':user_account_id', $lastInsertId);
            $stmt2->bindParam('first_name', $first_name);
            $stmt2->bindParam('last_name', $last_name);
            $stmt2->bindParam(':dob', $birth_date);
            $stmt2->bindParam('email', $email);
            $stmt2->bindParam('mobile', $mobile);
            $stmt2->bindParam('gender', $gender);
            $stmt2->execute();

        } else {
            echo "Failed to create user account.";
        }

    } catch (PDOException $e) {
        die("PDO Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
    <title>Document</title>
</head>

<body>

    <form action="" method="post">
        <label for="username">Username:</label><br>
        <input type="text" id="username" name="username" placeholder="username"><br><br>

        <label for="firstName">First Name:</label><br>
        <input type="text" id="firstName" name="firstName" placeholder="first name"><br><br>

        <label for="lastName">Last Name:</label><br>
        <input type="text" id="lastName" name="lastName" placeholder="last name"><br><br>

        <label for="month">Month:</label>
        <select id="month" name="month">
            <option value="">-- Select Month --</option>
            <option value="1">January</option>
            <option value="2">February</option>
            <option value="3">March</option>
            <option value="4">April</option>
            <option value="5">May</option>
            <option value="6">June</option>
            <option value="7">July</option>
            <option value="8">August</option>
            <option value="9">September</option>
            <option value="10">October</option>
            <option value="11">November</option>
            <option value="12">December</option>
        </select><br><br>

        <label for="day">Day:</label>
        <select id="day" name="day">
            <option value="">-- Select Day --</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="6">6</option>
            <option value="7">7</option>
            <option value="8">8</option>
            <option value="9">9</option>
            <option value="10">10</option>
            <option value="11">11</option>
            <option value="12">12</option>
            <option value="13">13</option>
            <option value="14">14</option>
            <option value="15">15</option>
            <option value="16">16</option>
            <option value="17">17</option>
            <option value="18">18</option>
            <option value="19">19</option>
            <option value="20">20</option>
            <option value="21">21</option>
            <option value="22">22</option>
            <option value="23">23</option>
            <option value="24">24</option>
            <option value="25">25</option>
            <option value="26">26</option>
            <option value="27">27</option>
            <option value="28">28</option>
            <option value="29">29</option>
            <option value="30">30</option>
            <option value="31">31</option>
        </select><br><br>

        <label for="year">Year:</label>
        <input type="number" id="year" name="year" min="1900" max="2100"><br><br>

        <fieldset>
            <legend>Gender</legend>

            <input type="radio" id="male" name="gender" value="male">
            <label for="male">Male</label><br>

            <input type="radio" id="female" name="gender" value="female">
            <label for="female">Female</label><br>

            <input type="radio" id="other" name="gender" value="other">
            <label for="other">Other</label>

        </fieldset> <br><br>

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" placeholder="email"><br><br>

        <label for="mobile">Mobile:</label><br>
        <input type="text" id="mobile" name="mobile" placeholder="mobile"><br><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" placeholder="password"><br><br>

        <button type="submit" value="Submit" name="registration">submit</button>
    </form>

</body>

</html>