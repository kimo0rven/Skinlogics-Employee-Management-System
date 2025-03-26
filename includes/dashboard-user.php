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
                        <div class="profile-dropdown-content">
                            <a href="#">Profile Settings</a>
                            <a href="#">Account Details</a>
                            <a href="logout.php">Logout</a>
                        </div>
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
                        <div class="dashboard-chart">1</div>
                    </div>
                    <div class="dashboard-other-info"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById("logout-user").addEventListener("click", function () {
        window.location.href = "logout.php";
    });

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