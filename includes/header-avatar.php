<?php
include 'includes/database.php';

$user_account_id = (int) $_SESSION["user_account_id"];

try {
    $sql = "SELECT first_name, last_name FROM employee WHERE user_account_id = :user_account_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_account_id' => $user_account_id]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userInfo) {
        $first_name = $userInfo['first_name'];
        $last_name = $userInfo['last_name'];
    }
} catch (PDOException $e) {
    echo "Error fetching user information: " . $e->getMessage();
}

?>
<div id="profile-dropdown-trigger" class="flex flex-row flex-end gap-10 ">
    <img class="dashboard-content-header-img" src="assets/images/avatars/<?php echo $_SESSION['avatar'] ?>"
        alt="avatar">

    <div class="flex flex-row justify-center align-center gap-10">
        <div>
            <p><?php echo $first_name ?>
        </div>
        <div><img height="20px" width="20px" src="assets/images/icons/arrow-down-icon.png" alt=""></div>
    </div>

    </p>
</div>
<div class="profile-dropdown" id="profileDropdown">
    <ul>
        <li><a href="profile.php">View Profile</a></li>
        <li><a href="logout.php">Log Out</a></li>
    </ul>
</div>
<?php include('includes/fob.php') ?>


<script>
    const profileTrigger = document.getElementById('profile-dropdown-trigger');
    const profileDropdown = document.getElementById('profileDropdown');

    profileTrigger.addEventListener('click', function () {
        profileDropdown.style.display = (profileDropdown.style.display === 'block') ? 'none' : 'block';
    });

    document.addEventListener('click', function (event) {

        if (!profileTrigger.contains(event.target) && !profileDropdown.contains(event.target)) {

            profileDropdown.style.display = 'none';
        }
    });
</script>