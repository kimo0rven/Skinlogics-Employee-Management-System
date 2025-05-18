<?php

$currentPage = basename($_SERVER['PHP_SELF']);

if ($_SESSION['role_id'] == 1) {
    $navigationItems = [
        'dashboard.php' => 'Dashboard',
        'employees.php' => 'Employees',
        'payroll.php' => 'Payroll',
        'work.php' => 'Work',
        'departments.php' => 'Departments',
        'timer.php' => 'Timer',
        'leave_requests.php' => "Leave_Requests",
        'overtime.php' => 'Overtime'

    ];
} else if ($_SESSION['role_id'] == 2) {
    $navigationItems = [
        'dashboard.php' => 'Dashboard',
        'employees.php' => 'Employees',
        'payroll.php' => 'Payroll',
        'work.php' => 'Work',
        'departments.php' => 'Departments',
        'timer.php' => 'Timer',
        'leave_requests.php' => "Leave_Requests",
        'overtime.php' => 'Overtime',
        'view_payroll.php' => 'View_Payrolls'

    ];
} else if ($_SESSION['role_id'] == 3) {
    $navigationItems = [
        'dashboard.php' => 'Dashboard',
        'timer.php' => 'Timer',
        'leave_requests.php' => 'Leave_Requests',
        'overtime.php' => 'Overtime',
        'view_payroll.php' => 'View_Payroll'
    ];
}

if ($_SESSION['isHRManager'] || $_SESSION['role_id'] == 1) {
    $navigationItems['hr_manager_leave_requests.php'] = 'HR_Manager_Leave_Request_Approval';
    $navigationItems['hr_manager_overtime.php'] = 'HR_Manager_Overtime_Approval';

}

if ($_SESSION['isTeamLeader'] || $_SESSION['role_id'] == 1) {
    $navigationItems['tl_leave_requests.php'] = 'Team_Leader_Leave_Requests_Approval';
    $navigationItems['tl_overtime.php'] = 'Team_Leader_Overtime_Approval';

}

?>

<div class="navigation-container">
    <?php foreach ($navigationItems as $file => $label): ?>
        <div class="<?php echo ($currentPage === $file) ? 'active' : ''; ?> tooltip">
            <a href="/<?php echo $file; ?>">
                <img src="assets/images/icons/<?php echo strtolower($label); ?>-icon.png" height="32px" width="32px"
                    alt="<?php echo $label; ?>"><span class="tooltiptext font-medium"><?php echo str_replace("_", " ", $label);
                       ; ?></span>
            </a>
        </div>
    <?php endforeach; ?>


</div>