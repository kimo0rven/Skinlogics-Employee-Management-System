<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the input data (the time in this case)
    $timeNow = file_get_contents('php://input');

    echo $timeNow;

    // Check if time is present
    if (!empty($timeNow)) {
        // Process the data (e.g., store it in a database or log it)
        // Here, we're just logging it for demonstration
        $logFile = 'clockin_log.txt';
        file_put_contents($logFile, "Clock In Time: " . $timeNow . PHP_EOL, FILE_APPEND);

        // Send a success response
        http_response_code(200);
        echo 'Clock In successful!';
    } else {
        // If no data is received, send an error response
        http_response_code(400);
        echo 'No time data received.';
    }
} else {
    // If the request is not POST, send a 405 Method Not Allowed response
    http_response_code(405);
    echo 'Invalid request method.';
}
?>