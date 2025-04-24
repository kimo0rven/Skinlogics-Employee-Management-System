```php
<?php
session_start();
include('includes/database.php');

if (isset($_POST['registration'])) {
    // 1) Collect & sanitize inputs
    $first_name   = trim($_POST['firstName']);
    $last_name    = trim($_POST['lastName']);
    $username     = trim($_POST['username']);
    $email        = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $confirm_email= filter_var(trim($_POST['confirm_email']), FILTER_VALIDATE_EMAIL);
    $pass         = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];
    $year         = (int)$_POST['year'];
    $month        = (int)$_POST['month'];
    $day          = (int)$_POST['day'];
    $birth_date   = sprintf('%04d-%02d-%02d', $year, $month, $day);
    $mobile       = trim($_POST['mobile']);
    $gender       = $_POST['gender'];
    $accountType  = 'User';

    // 2) Validate
    if (!$email || !$confirm_email) {
        $error = 'Please enter a valid email.';
    } elseif ($email !== $confirm_email) {
        $error = 'Emails do not match.';
    } elseif ($pass !== $confirm_pass) {
        $error = 'Passwords do not match.';
    }

    if (empty($error)) {
        // 3) Hash password
        $passwordHash = password_hash($pass, PASSWORD_DEFAULT);
        try {
            // 4) Insert into user_account
            $sql  = "INSERT INTO user_account (username,email,pass,account_type) VALUES (:username,:email,:pass,:accountType)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':username',    $username);
            $stmt->bindParam(':email',       $email);
            $stmt->bindParam(':pass',        $passwordHash);
            $stmt->bindParam(':accountType', $accountType);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $success = 'Registration successful!';
                $userId  = $pdo->lastInsertId();
                $_SESSION['employee_id'] = $userId;

                // 5) Insert into employee
                $sql2  = "INSERT INTO employee (user_account_id,first_name,last_name,dob,email,mobile,gender)
                          VALUES (:userId,:first_name,:last_name,:dob,:email,:mobile,:gender)";
                $stmt2 = $pdo->prepare($sql2);
                $stmt2->bindParam(':userId',     $userId);
                $stmt2->bindParam(':first_name', $first_name);
                $stmt2->bindParam(':last_name',  $last_name);
                $stmt2->bindParam(':dob',        $birth_date);
                $stmt2->bindParam(':email',      $email);
                $stmt2->bindParam(':mobile',     $mobile);
                $stmt2->bindParam(':gender',     $gender);
                $stmt2->execute();
            } else {
                $error = 'Failed to create user account.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <style>
    body {
      background-color: #f0f7f4;
      font-family: Arial, sans-serif;
    }
    .form-card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
      padding: 40px;
      max-width: 500px;
      margin: 50px auto;
    }
    .form-card input,
    .form-card select,
    .form-card button {
      width: 100%;
      padding: 12px;
      margin-top: 10px;
      margin-bottom: 20px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
      box-sizing: border-box;
    }
    .form-card button {
      background-color: #007bff;
      color: #fff;
      font-weight: bold;
      border: none;
      cursor: pointer;
      transition: background-color 0.3s;
    }
    .form-card button:hover {
      background-color: #0056b3;
    }
    .dob-row {
      display: flex;
      gap: 10px;
    }
    .dob-row input,
    .dob-row select {
      flex: 1;
    }
    fieldset {
      border: none;
      margin-bottom: 20px;
    }
    .gender-options {
      display: flex;
      gap: 20px;
    }
    .gender-options label {
      display: flex;
      align-items: center;
      font-size: 14px;
    }
    .gender-options input {
      margin-right: 8px;
    }
    .message {
      text-align: center;
      margin-bottom: 16px;
      font-weight: bold;
    }
    .message.error { color: #d9534f; }
    .message.success { color: #5cb85c; }
  </style>
</head>
<body>
  <div class="form-card">
    <?php if (!empty($error)): ?>
      <div class="message error"><?php echo $error; ?></div>
    <?php elseif (!empty($success)): ?>
      <div class="message success"><?php echo $success; ?></div>
    <?php endif; ?>
    <form action="" method="post">
      <input type="text" name="firstName" placeholder="First Name" required>
      <input type="text" name="lastName" placeholder="Last Name" required>
      <input type="text" name="username" placeholder="Username" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="email" name="confirm_email" placeholder="Confirm Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <input type="password" name="confirm_password" placeholder="Confirm Password" required>
      <div class="dob-row">
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
        <input type="number" name="day" placeholder="Day (1–31)" min="1" max="31" required>
      </div>
      <input type="text" name="mobile" placeholder="Mobile" required>
      <fieldset>
        <legend>Gender</legend>
        <div class="gender-options">
          <label><input type="radio" name="gender" value="male" required>Male</label>
          <label><input type="radio" name="gender" value="female">Female</label>
          <label><input type="radio" name="gender" value="other">Other</label>
        </div>
      </fieldset>
      <button type="submit" name="registration" value="1">Register</button>
    </form>
  </div>
</body>
</html>
```
