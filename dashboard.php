<?php
session_start();
if (isset($_SESSION["user_account_id"]) && isset($_SESSION['username'])) {
    $user_account_id = $_SESSION["user_account_id"];

    $accountType = $_SESSION["role_id"];

    if ($accountType === 3) {
        echo '<style>.admin { display: none; }</style>';
    } elseif ($accountType === 1 || $accountType === 2) {
        echo '<style>.member { display: none; }</style>';
    } else {

        echo '<style>.admin, .member { display: none; }</style>';
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
    <link rel="stylesheet" href="user-side.css" />
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.min.js"></script>
</head>

<body>
    <div class="dashboard-background">
        <div class="dashboard-container">
            <div class="dashboard-navigation">
                <?php include('includes/navigation.php') ?>

            </div>
            <div class="dashboard-content">
                <div class="dashboard-content-item1">
                    <div class="dashboard-content-header font-black">
                        <h1>DASHBOARD</h1>
                    </div>
                    <div id="logout-admin" class="dashboard-content-header font-medium">
                        <?php include('includes/header-avatar.php') ?>
                    </div>
                </div>
                <div id="admin">
                    <?php include('includes/dashboard-admin.php'); ?>
                </div>

                <div id="member">
                    <?php include('includes/dashboard-user.php'); ?>
                </div>
            </div>
        </div>
    </div>


</body>
<script>


    let accountType = <?php echo json_encode($accountType); ?>;
    console.log(accountType)
    adminBody = document.getElementById('admin');
    memberBody = document.getElementById('member')

    if (accountType == "1" || accountType == "2") {
        adminBody.classList.remove('hidden');
        memberBody.classList.add('hidden');
    } else if (accountType == "3") {
        memberBody.classList.remove('hidden');
        adminBody.classList.add('hidden');
    }


</script>

</html>