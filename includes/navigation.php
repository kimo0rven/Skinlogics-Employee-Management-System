<?php

$currentPage = basename($_SERVER['PHP_SELF']);

$accountType = $_SESSION["account_type"];

if ($accountType == 'Admin') {
    $navigationItems = [
        'dashboard.php' => 'Dashboard',
        'employees.php' => 'Employees',
        'payroll.php' => 'Payroll',
        'timer.php' => 'Timer',
    ];
} else if ($accountType == 'User') {
    $navigationItems = [
        'dashboard.php' => 'Dashboard',
        'timer.php' => 'Timer',

    ];
}
?>

<div class="navigation-container">
    <?php foreach ($navigationItems as $file => $label): ?>
        <div class="<?php echo ($currentPage === $file) ? 'active' : ''; ?>">
            <a href="/<?php echo $file; ?>">
                <img src="assets/images/icons/<?php echo strtolower($label); ?>-icon.png" alt="<?php echo $label; ?>">
            </a>
        </div>

    <?php endforeach; ?>
    <!-- <div>
        <a href="logout.php">
            <img height="32px" width="32px" src="assets/images/icons/logout-icon.png">
        </a>
    </div> -->
</div>