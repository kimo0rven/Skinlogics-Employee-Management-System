<?php

$currentPage = basename($_SERVER['PHP_SELF']);

if ($_SESSION['role_id'] == 1) {
    $navigationItems = [
        'dashboard.php' => 'Dashboard',
        'employees.php' => 'Employees',
        'payroll.php' => 'Payroll',
        'work.php' => 'Work',
        'departments.php' => 'Departments'
    ];
} else if ($_SESSION['role_id'] == 2) {
    $navigationItems = [
        'dashboard.php' => 'Dashboard',
        'timer.php' => 'Timer',

    ];
}

if ($_SESSION['isHRManager']) {
    $navigationItems['hr_manager_approve.php'] = 'HR_Manager_Approval';
}

if ($_SESSION['isTeamLeader']) {
    $navigationItems['isTeamLeader.php'] = 'Team_Leader';
}
?>

<div class="navigation-container">
    <?php foreach ($navigationItems as $file => $label): ?>
        <div class="<?php echo ($currentPage === $file) ? 'active' : ''; ?>">
            <a href="/<?php echo $file; ?>">
                <img src="assets/images/icons/<?php echo strtolower($label); ?>-icon.png" height="32px" width="32px"
                    alt="<?php echo $label; ?>">
            </a>
        </div>
    <?php endforeach; ?>


</div>