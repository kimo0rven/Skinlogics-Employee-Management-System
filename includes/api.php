<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (isset($data['eId'])) {
            $receivedData = htmlspecialchars($data['eId']);

            $dataObject = [
                'id' => $receivedData
            ];
            $_SESSION['edit_id'] = $receivedData;

            echo json_encode(['status' => 'success', 'data' => $dataObject]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Missing employee ID']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method. Only POST allowed.']);
    }
    exit();
}
?>