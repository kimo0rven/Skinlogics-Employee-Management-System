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

<p><?php echo $first_name . " " . $last_name ?></p>
<img class="dashboard-content-header-img profile-dropdown-trigger"
    src="assets/images/avatars/<?php echo $_SESSION['avatar'] ?>" alt="">
<div class="profile-dropdown" id="profileDropdown">
    <ul>
        <li><a href="profile.php">View Profile</a></li>
        <li><a href="logout.php">Log Out</a></li>
    </ul>
</div>

<script>
    const profileTrigger = document.querySelector('.profile-dropdown-trigger');
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