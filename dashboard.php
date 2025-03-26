<?php
session_start();

if (isset($_SESSION["user_account_id"]) && isset($_SESSION['username'])) {
    $user_account_id = $_SESSION["user_account_id"];

    $accountType = $_SESSION["account_type"];

    if ($accountType === "member") {
        echo '<style>.admin { display: none; }</style>';
    } elseif ($accountType === "admin") {
        echo '<style>.member { display: none; }</style>';
    } else {

        echo '<style>.admin, .member { display: none; }</style>'; // Or display both if no specific action desired.
    }


    include 'includes/database.php';
    include 'config.php';

    try {
        $sql = "SELECT * FROM employee WHERE user_account_id = :user_account_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_account_id', $user_account_id, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) {

            foreach ($rows as $row) {
                $first_name = $row['first_name'];
                $last_name = $row['last_name'];
            }
        } else {
            echo "No records found for user account ID: " . $user_account_id;
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> | Dashboard</title>
    <link rel="stylesheet" href="style.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.min.js"></script>
</head>

<body>
    <div id="admin">
        <?php include('includes/dashboard-admin.php'); ?>
    </div>

    <div id="member">
        <?php include('includes/dashboard-user.php'); ?>
    </div>
</body>
<script>


    let accountType = <?php echo json_encode($accountType); ?>;
    console.log(accountType)
    adminBody = document.getElementById('admin');
    memberBody = document.getElementById('member')

    if (accountType == "Admin") {
        adminBody.classList.remove('hidden');
        memberBody.classList.add('hidden');
    } else if (accountType == "User") {
        memberBody.classList.remove('hidden');
        adminBody.classList.add('hidden');
    }


</script>

</html>