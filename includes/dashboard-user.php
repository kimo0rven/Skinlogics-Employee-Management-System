<?php
$message = '';
date_default_timezone_set('Asia/Manila');
$currentTime = date('Y-m-d h:i:s A'); // Use 'h' for 12-hour format and 'A' for AM/PM

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['clock_in'])) {
        // Handle clock in
        $_SESSION['clock_in_time'] = $currentTime;
        $message = "Clocked in successfully at " . $_SESSION['clock_in_time'];
    } elseif (isset($_POST['clock_out'])) {
        // Handle clock out
        if (isset($_SESSION['clock_in_time'])) {
            $clockInTime = $_SESSION['clock_in_time'];
            $clockOutTime = $currentTime;

            // Calculate time worked (in seconds for this example)
            $timeWorked = strtotime($clockOutTime) - strtotime($clockInTime);
            $hours = floor($timeWorked / 3600);
            $minutes = floor(($timeWorked % 3600) / 60);
            $seconds = $timeWorked % 60;

            $message = "Clocked out successfully at $clockOutTime. Time worked: $hours hours, $minutes minutes, $seconds seconds";

            // Clear the clock in time
            unset($_SESSION['clock_in_time']);
        } else {
            $message = "You need to clock in first!";
        }
    }
}
?>


<div class="dashboard-content-item2">
    <div class="dashboard-main-content">
        <div class="dashboard-kpi font-medium">
            <div class="kpi-item">
                <p class="kpi-label">Vacation Leaves</p>
                <div class="kpi-value-container">
                    <p class="kpi-value"><?php echo $employeeCount; ?></p>
                    <img src="./assets/images/employee.png" class="kpi-icon" alt="Employee Icon">
                </div>
            </div>
            <div class="kpi-item">
                <p class="kpi-label">Sick Leaves</p>
                <div class="kpi-value-container">
                    <p class="kpi-value"><?php echo $activeCount; ?></p>
                    <img src="./assets/images/active.png" class="kpi-icon" alt="Employee Icon">
                </div>
            </div>
            <div class="kpi-item">
                <p class="kpi-label">Lates</p>
                <div class="kpi-value-container">
                    <p class="kpi-value"><?php echo $resignedCount; ?></p>
                    <img src="./assets/images/resign.png" class="kpi-icon" alt="Employee Icon">
                </div>
            </div>
            <div class="kpi-item">
                <p class="kpi-label">Absents</p>
                <div class="kpi-value-container">
                    <p class="kpi-value"><?php echo $terminatedCount; ?></p>
                    <img src="./assets/images/terminated.png" class="kpi-icon" alt="Employee Icon">
                </div>
            </div>
        </div>
        <div class="dashboard-chart">

            <br>
        </div>
    </div>
    <div class="dashboard-other-info border-red">

        1
    </div>
</div>

<script>
    function displayCurrentTime12Hour() {
        const now = new Date();
        let hours = now.getHours();
        const minutes = now.getMinutes().toString().padStart(2, '0');
        const seconds = now.getSeconds().toString().padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';

        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'

        const timeString = `${hours}:${minutes}:${seconds} ${ampm}`;

        document.getElementById('timeDisplay').textContent = timeString;
    }

    displayCurrentTime12Hour();
    setInterval(displayCurrentTime12Hour, 1000);

    document.addEventListener('DOMContentLoaded', function () {
        const dropdownContainer = document.querySelector('.profile-dropdown-container');
        const dropdownTrigger = document.querySelector('.profile-dropdown-trigger');

        dropdownTrigger.addEventListener('click', function (event) {
            event.stopPropagation();
            dropdownContainer.classList.toggle('show');
        });

        document.addEventListener('click', function (event) {
            if (!event.target.closest('.profile-dropdown-container')) {
                dropdownContainer.classList.remove('show');
            }
        });
    });
</script>