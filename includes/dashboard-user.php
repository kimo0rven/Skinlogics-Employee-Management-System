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


<div>
    <div class="dashboard-background">
        <div class="dashboard-container">
            <div class="dashboard-navigation">
                <div class=".navigation-container ">
                    <div class="navigation-button">1</div>
                    <div class="navigation-button">2</div>
                    <div class="navigation-button">3</div>
                    <div class="navigation-button">4</div>
                    <div class="navigation-button">5</div>
                    <div class="navigation-button">6

                    </div>
                </div>

            </div>
            <div class="dashboard-content">
                <div class="dashboard-content-item1">
                    <div class="dashboard-content-header font-black">
                        <h1>DASHBOARD</h1>
                    </div>
                    <div id="logout-user" class="dashboard-content-header font-medium profile-dropdown-container">
                        <p><?php echo $first_name . " " . $last_name ?></p>
                        <img class="dashboard-content-header-img profile-dropdown-trigger"
                            src="assets/images/avatars/<?php echo $_SESSION['avatar'] ?>" alt="">

                    </div>
                </div>
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
                        <div class="dashboard-chart user-timer-container">
                            <div class="user-timer" class="">Current Time: <p id="timeDisplay" class="font-size-32"></p>
                            </div>
                            <form class="user-timer-form" action="" method="POST">
                                <div class="user-timer user-timer-container">

                                    <div><button type="submit" class="user-timer-button user-timer-button-start"
                                            name="clock_in" type="button">Clock
                                            In</button></div>

                                    <div><button type="submit" class="user-timer-button user-timer-button-end"
                                            name="clock_out" type="button">Clock
                                            Out</button>
                                    </div>

                                    <div>
                                        <p><?php echo htmlspecialchars($message); ?></p>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="dashboard-other-info"></div>
                </div>
            </div>
        </div>
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