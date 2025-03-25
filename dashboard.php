<?php
session_start();
if (isset($_SESSION["employee_id"]) && isset($_SESSION['email'])) {
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <h1>Hello <?php echo $_SESSION['gender'] ?></h1>
</body>

</html>