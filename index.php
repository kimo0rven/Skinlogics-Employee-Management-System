<?php

include 'config.php';
include 'js_php/database.php';

date_default_timezone_set('Asia/Manila');
$currentHour = date('G');

if ($currentHour >= 5 && $currentHour < 12) {
  $greeting = "Good Morning!";
} elseif ($currentHour >= 12 && $currentHour < 18) {
  $greeting = "Good Afternoon!";
} else {
  $greeting = "Good Evening!";
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $title; ?> | Login</title>
  <link rel="stylesheet" href="style.css" />
</head>

<body>
  <div class="background font-regular">
    <div class="container">
      <div class="left-section">

      </div>
      <div class="right-section">
        <img src="./assets/images/logo.png" height="40%" width="40%" alt="">
        <h2><?php echo $greeting; ?> Welcome Back</h2>

        <form action="login.php" method="POST">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" required>
          <label for="pass">Password</label>
          <input type="password" id="pass" name="pass" required>
          <button type="submit" name="submit" value="login">Login</button>
        </form>
      </div>
    </div>
  </div>
</body>

</html>