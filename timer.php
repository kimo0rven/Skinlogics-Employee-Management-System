<?php
session_start();

if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

include 'includes/database.php';
include 'config.php';


date_default_timezone_set('Asia/Manila');

$day = date('j');
$month = date('M');
$year = date('Y');
$dayOfWeekNumber = date('w');
$dayOfWeekText = date('l');
$daysOfWeek = array(
    0 => 'Sunday',
    1 => 'Monday',
    2 => 'Tuesday',
    3 => 'Wednesday',
    4 => 'Thursday',
    5 => 'Friday',
    6 => 'Saturday'
);
$dayOfWeekFromArr = $daysOfWeek[date('w')];

$hour = date('h');
$minutes = date('i');
$seconds = date('s');
$amPm = date('A');
$currentDate = date('Y-m-d');

$attendanceRecord = "";
try {

    $stmt = $pdo->prepare('SELECT * FROM attendance WHERE employee_id = :employee_id AND date = :date');
    $stmt->bindParam(':employee_id', $_SESSION['employee_id']);
    $stmt->bindParam(':date', $currentDate);
    $stmt->execute();
    $attendanceRecord = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($attendanceRecord);

} catch (PDOException $e) {
    echo '' . $e->getMessage() . '';
}

$clockInTimeFormatted = "";
$clockOutTimeFormatted = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['clock-in'])) {
        $clockInTime = date('h:i:s');
        $clockInTimeFormatted = date('h:i A');
        $status = '';

        try {

            if (!$attendanceRecord) {
                // No attendance record exists; proceed to insert
                $clockInTime = date('H:i:s');
                $clockInTimeFormatted = date('h:i A');

                $clockInTimeDate = $currentDate . ' ' . $clockInTime;

                $cutOffStartTime = $currentDate . ' 09:00:00';
                $cutOffEndTime = $currentDate . ' 18:00:00';

                $clockInTimestamp = strtotime($clockInTimeDate);
                $cutOffStartTimestamp = strtotime($cutOffStartTime);
                $cutOffEndTimestamp = strtotime($cutOffEndTime);

                if ($clockInTimestamp <= $cutOffStartTimestamp) {
                    $status = 'Present';
                } elseif ($clockInTimestamp > $cutOffStartTimestamp && $clockInTimestamp <= $cutOffEndTimestamp) {
                    $status = 'Late';
                } else {
                    $status = 'Absent';
                }

                $sql = 'INSERT INTO attendance (employee_id, date, clock_in_time, status) VALUES (:employee_id, :date, :clock_in_time, :status)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':employee_id', $employee_id);
                $stmt->bindParam(':date', $currentDate);
                $stmt->bindParam(':clock_in_time', $clockInTime);
                $stmt->bindParam(':status', $status);

                $stmt->execute();
            } else {
                echo "Attendance record already exists for today.";
            }

        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

}

?>

<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Admin'; ?> | Clock</title>
    <link rel="stylesheet" href="style.css" />
</head>


<body class="font-medium">
    <div id="admin">
        <div class="dashboard-background">
            <div class="dashboard-container">
                <div class="dashboard-navigation">
                    <?php include('includes/navigation.php') ?>
                </div>
                <div class="dashboard-content">
                    <div class="dashboard-content-item1">
                        <div class="dashboard-content-header font-black">
                            <h1>CLOCK IN & OUT</h1>
                        </div>
                        <div id="logout-admin" class="dashboard-content-header font-medium">
                            <?php include('includes/header-avatar.php') ?>
                        </div>
                    </div>

                    <div class="employee-main-content">
                        <div class="employee-display">
                            <div class="timer-container font-medium">
                                <div class="timer-datetime gap-20"
                                    style="padding: 20 0; color: var(--color-accent-content)">
                                    <div>
                                        <p class=" font-bold font-size-20" style="margin: 0">
                                            <?php echo $dayOfWeekFromArr . ", " . $month . " " . $day . ", " . $year ?>
                                        </p>
                                    </div>
                                    <div class="flex flex-row justify-center gap-10">
                                        <div id="timeDisplay" class="font-size-40"></div>
                                        <div class="flex flex-column justify-center">
                                            <div id="secondsDisplay"></div>
                                            <div id="amPmDisplay"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="">

                                    <div class="flex flex-column justify-center align-center gap-20">

                                        <?php
                                        if ($clockInTimeFormatted) {
                                            echo '<div> Clock In Time: <strong>' . $clockInTimeFormatted . '</strong></div>';
                                        } else {
                                            echo '<div style="opacity: 0">1</div>';
                                        }
                                        ?>
                                        <div class="flex flex-row justify-center gap-20">
                                            <div>
                                                <form id="clockInForm" action="/timer.php" method="POST">
                                                    <?php
                                                    if ($attendanceRecord) {
                                                        echo 1;
                                                    } else {
                                                        echo 2;
                                                    }
                                                    // <button id="ClockIn" name="clock-in" type="submit"
                                                    //     class="clock-buttons" style="background-color: #5cb85b;"
                                                    //     onclick="getCurrentTime()">Clock
                                                    //     In</button>
                                                    ?>
                                                </form>
                                            </div>
                                            <form action="#"></form>
                                            <div><button id="ClockOut" class="clock-buttons"
                                                    style="background-color: #d9534f;" onclick="getCurrentTime()">Clock
                                                    Out</button></div>
                                        </div>
                                        <div class="flex flex-column flex-start">
                                            <p style="text-align: start; margin:0">Comment:</p>
                                            <div><textarea class="timer-remarks"></textarea>
                                            </div>
                                            </form>
                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

</body>
<div class="flex flex-row justify-center gap-10">
    <div id="timeDisplay" class="font-size-40"></div>
    <div class="flex flex-column justify-center">
        <div id="secondsDisplay"></div>
        <div id="amPmDisplay"></div>
    </div>
</div>

<script>
    function formatTime(date) {
        let hours = date.getHours();
        const minutes = date.getMinutes();
        const seconds = date.getSeconds();
        const amPm = hours >= 12 ? 'PM' : 'AM';

        hours = hours % 12 || 12;

        const formattedHours = String(hours).padStart(2, '0');
        const formattedMinutes = String(minutes).padStart(2, '0');
        const formattedSeconds = String(seconds).padStart(2, '0');

        return {
            time: `${formattedHours}:${formattedMinutes}`,
            seconds: formattedSeconds,
            amPm: amPm,
        };
    }

    function displayTime() {
        const now = new Date();
        const { time, seconds, amPm } = formatTime(now);

        document.getElementById('timeDisplay').textContent = time;
        document.getElementById('secondsDisplay').textContent = seconds;
        document.getElementById('amPmDisplay').textContent = amPm;
    }

    displayTime();
    setInterval(displayTime, 1000);

    const now = new Date();
    const timeNow = `${now.getHours()}:${now.getMinutes()}:${now.getSeconds()}`;
    console.log(timeNow);
    document.getElementById('clockInForm').addEventListener('submit', function (event) {
    });

</script>

</html>