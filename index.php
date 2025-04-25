<?php
session_start();
if (isset($_SESSION["user_account_id"]) && isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}

include 'config.php';

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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo $title; ?> | Login</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
</head>

<body>
  <div class="login-container">
    <div class="login-container-div">
      <div class="text-section">
        <h1 class="text-section-title">A Place To Unlock Your Team's Potential</h1>
        <p class="subtext"><?php echo $greeting; ?> Empower your team with efficient management tools. Manage
          performance, foster growth, and achieve your goals.</p>
        <form class="login-form" method="POST" action="login.php">
          <input class="login-input" type="text" name="username" placeholder="Username" required>
          <input class="login-input" type="password" name="pass" placeholder="Password" required>

          <div class="login-button-container">
            <button class="login-button" type="submit" name="submit">Log in</button>
          </div>
        </form>


      </div>

      <div class="mockup-section">
        <img src="assets/images/office-mockup.png" alt="Messenger Mockup">
      </div>
    </div>

  </div>
</body>

</html>