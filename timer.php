<?php
session_start();

if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

require_once 'includes/database.php';
require_once 'config.php';

date_default_timezone_set('Asia/Manila');
$day = date('j');
$month = date('M');
$year = date('Y');
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
$currentDate = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime($currentDate . ' -1 day'));

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
function fetchAttendanceRecord($pdo, $date)
{
    $stmt = $pdo->prepare('SELECT * FROM attendance WHERE employee_id = :employee_id AND date_created = :date');
    $stmt->execute([':employee_id' => $_SESSION['employee_id'], ':date' => $date]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$attendanceRecord = [];
try {
    $attendanceRecord = fetchAttendanceRecord($pdo, $currentDate);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

try {
    $yesterdayAttendance = fetchAttendanceRecord($pdo, $yesterday);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

$clockInTimeFormatted = "";
$clockOutTimeFormatted = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['clock-in'])) {
        $clockInTime = date('Y-m-d H:i:s');

        if (!$yesterdayAttendance) {
            $sql = 'INSERT INTO attendance (employee_id, date_created, status, worked_hours) 
                        VALUES (:employee_id, :date, :status, :worked_hours)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':employee_id' => $_SESSION['employee_id'],
                ':date' => $yesterday,
                ':status' => 'Absent',
                'worked_hours' => 0
            ]);
        }

        if (!$attendanceRecord) {
            try {
                $cutOffStartTimestamp = strtotime($currentDate . ' 09:00:00');
                $clockInTimestamp = strtotime($clockInTime);

                $status = ($clockInTimestamp <= $cutOffStartTimestamp) ? 'Present' : 'Late';

                $sql = 'INSERT INTO attendance (employee_id, date_created, clock_in_time, status) 
                        VALUES (:employee_id, :date, :clock_in_time, :status)';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':employee_id' => $_SESSION['employee_id'],
                    ':date' => $currentDate,
                    ':clock_in_time' => $clockInTime,
                    ':status' => $status
                ]);

                $attendanceRecord = fetchAttendanceRecord($pdo, $currentDate);
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
        }
    }

    if (isset($_POST["clock-out"])) {
        try {
            $attendanceRecord = fetchAttendanceRecord($pdo, $currentDate);

            if ($attendanceRecord && (empty($attendanceRecord['clock_out_time']) || $attendanceRecord['clock_out_time'] == "0000-00-00 00:00:00")) {
                $clockOutTime = date('Y-m-d H:i:s');

                $clockInTimestamp = strtotime($attendanceRecord['clock_in_time']);
                $clockOutTimestamp = strtotime($clockOutTime);
                $differenceInSeconds = $clockOutTimestamp - $clockInTimestamp;
                $workedHours = number_format($differenceInSeconds / 3600, 2);

                $sql = 'UPDATE attendance 
                        SET clock_out_time = :clock_out_time, worked_hours = :worked_hours 
                        WHERE employee_id = :employee_id AND date_created = :date';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':clock_out_time' => $clockOutTime,
                    ':worked_hours' => $workedHours,
                    ':employee_id' => $_SESSION['employee_id'],
                    ':date' => $currentDate
                ]);
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
$clockIn = false;
$clockOut = false;

if ($attendanceRecord) {
    if ($attendanceRecord['clock_in_time']) {
        $clockIn = true;
        $dateTime = new DateTime($attendanceRecord['clock_in_time']);
        $clockInTimeFormatted = $dateTime->format('h:i A');
    }

    if ($attendanceRecord['clock_out_time']) {
        $clockOut = true;
        $dateTime = new DateTime($attendanceRecord['clock_out_time']);
        $clockOutTimeFormatted = $dateTime->format('h:i A');
    }
}

?>

<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Admin'; ?> | Clock</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
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
                                        <div class="flex flex-row gap-20">
                                            <?php
                                            if ($clockIn) {
                                                echo '<div> Clock In Time: <strong>' . $clockInTimeFormatted . '</strong></div>';
                                            } else {
                                                echo '<div style="opacity: 0">1</div>';
                                            }
                                            if ($clockOut) {
                                                echo '<div> Clock Out Time: <strong>' . $clockOutTimeFormatted . '</strong></div>';
                                            } else {
                                                echo '<div style="opacity: 0">1</div>';
                                            }
                                            ?>
                                        </div>
                                        <div class="flex flex-row justify-center gap-20">
                                            <div>
                                                <form id="clockInForm" action="/timer.php" method="POST">
                                                    <?php
                                                    if ($attendanceRecord) {
                                                        echo '<button id="ClockIn" name="clock-in" type="submit"
                                                        class="clock-buttons" onclick="getCurrentTime()" disabled>Clock
                                                        In!</button>';
                                                    } else {
                                                        echo '<button id="ClockIn" name="clock-in" type="submit"
                                                        class="clock-buttons" onclick="getCurrentTime()">Clock
                                                        In!</button>';
                                                    }
                                                    ?>
                                                </form>
                                            </div>
                                            <form id="clockOutForm" action="/timer.php" method="POST">
                                                <?php
                                                if ($attendanceRecord) {
                                                    echo '<button id="ClockOut" name="clock-out" type="submit"
                                                    class="clock-buttons" onclick="getCurrentTime()">Clock Out</button>';
                                                } else {
                                                    echo '<button id="ClockOut" name="clock-out" type="submit"
                                                    class="clock-buttons" disabled>Clock Out</button>';
                                                }
                                                ?>

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