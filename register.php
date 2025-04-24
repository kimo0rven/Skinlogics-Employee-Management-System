<?php
include('includes/database.php');

if (isset($_POST['registration'])) {
    $first_name = $_POST['firstName'];
    $last_name = $_POST['lastName'];
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $birth_date = $_POST['year'] . "-" . $_POST['month'] . "-" . $_POST['day'];
    $gender = $_POST['gender'];
    $mobile = $_POST['mobile'];
    $username = $_POST['username'];

    $sql = "INSERT INTO user_account (username, email, pass)
            VALUES ('$username', '$email', '$pass')";
    // Removed exec($sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration Form</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .form-wrapper {
            width: 100%;
            max-width: 600px;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .row {
            display: flex;
            gap: 15px;
        }

        input,
        select {
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
            width: 100%;
        }

        .gender-row {
            display: flex;
            justify-content: space-between;
            gap: 15px;
        }

        .gender-option {
            flex: 1;
            display: flex;
            align-items: center;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            cursor: pointer;
        }

        .gender-option input {
            margin-right: 10px;
        }

        button {
            padding: 12px;
            font-size: 16px;
            border: none;
            background-color: #1a73e8;
            color: white;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0f5ac6;
        }
    </style>
</head>
<body>

<div class="form-wrapper">
    <form action="" method="post">
        <!-- First Name + Last Name -->
        <div class="row">
            <input type="text" name="firstName" placeholder="First Name" required>
            <input type="text" name="lastName" placeholder="Last Name" required>
        </div>

        <!-- Username -->
        <input type="text" name="username" placeholder="Username" required>

        <!-- Email + Confirm Email -->
        <input type="email" name="email" placeholder="Email" required>
        <input type="email" name="confirm_email" placeholder="Confirm Email" required>

        <!-- Password + Confirm Password -->
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>

        <!-- Birthday -->
        <div class="row">
            <input type="number" name="year" placeholder="Year (e.g. 2000)" min="1900" max="2100" required>
            <select name="month" required>
                <option value="">Month</option>
                <option value="01">January</option>
                <option value="02">February</option>
                <option value="03">March</option>
                <option value="04">April</option>
                <option value="05">May</option>
                <option value="06">June</option>
                <option value="07">July</option>
                <option value="08">August</option>
                <option value="09">September</option>
                <option value="10">October</option>
                <option value="11">November</option>
                <option value="12">December</option>
            </select>
            <input type="number" name="day" placeholder="Day (1-31)" min="1" max="31" required>
        </div>

        <!-- Mobile -->
        <input type="text" name="mobile" placeholder="Mobile" required>

        <!-- Gender -->
        <div class="gender-row">
            <label class="gender-option">
                <input type="radio" name="gender" value="male" required> Male
            </label>
            <label class="gender-option">
                <input type="radio" name="gender" value="female"> Female
            </label>
            <label class="gender-option">
                <input type="radio" name="gender" value="other"> Other
            </label>
        </div>

        <button type="submit" name="registration">Submit</button>
    </form>
</div>

</body>
</html>
