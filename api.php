<?php
start_session();
// api.php

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set the response header to indicate JSON
header('Content-Type: application/json');

// Allow requests from any origin (for local development, be more specific in production)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS preflight requests (for CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $rawData = file_get_contents('php://input');

    // Decode the JSON data
    $data = json_decode($rawData, true);

    // Check if JSON decoding was successful
    if ($data !== null) {
        // Process the received data
        $name = isset($data['name']) ? htmlspecialchars($data['name']) : '';
        $email = isset($data['email']) ? filter_var($data['email'], FILTER_SANITIZE_EMAIL) : '';

        // Example: Log the data (replace with your actual processing)
        error_log("Received data: Name - " . $name . ", Email - " . $email);

        // Prepare the response
        $response = [
            'status' => 'success',
            'message' => 'Data received and processed successfully!',
            'receivedData' => [
                'name' => $name,
                'email' => $email
            ]
        ];
    } else {
        // Handle JSON decoding errors
        http_response_code(400); // Bad Request
        $response = [
            'status' => 'error',
            'message' => 'Invalid JSON data received.'
        ];
    }
} else {
    // Handle other request methods
    http_response_code(405); // Method Not Allowed
    $response = [
        'status' => 'error',
        'message' => 'Only POST requests are allowed.'
    ];
}

// Send the JSON response
echo json_encode($response);

?>