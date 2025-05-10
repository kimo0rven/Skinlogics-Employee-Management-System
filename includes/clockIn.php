<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $timeNow = file_get_contents('php://input');

    echo $timeNow;

    if (!empty($timeNow)) {
        $logFile = 'clockin_log.txt';
        file_put_contents($logFile, "Clock In Time: " . $timeNow . PHP_EOL, FILE_APPEND);

        http_response_code(200);
        echo 'Clock In successful!';
    } else {
        http_response_code(400);
        echo 'No time data received.';
    }
} else {
    http_response_code(405);
    echo 'Invalid request method.';
}
?>