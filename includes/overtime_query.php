<?php

include 'database.php';
header('Content-Type: application/json');

// Validate that all required parameters have been provided
if (!isset($_GET['employee_id']) || empty($_GET['employee_id'])) {
    echo json_encode(['error' => 'Employee ID is required']);
    exit;
}

if (!isset($_GET['start_date']) || empty($_GET['start_date'])) {
    echo json_encode(['error' => 'Start Date is required']);
    exit;
}

if (!isset($_GET['end_date']) || empty($_GET['end_date'])) {
    echo json_encode(['error' => 'End Date is required']);
    exit;
}

$employee_id = $_GET['employee_id'];
$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];

function calculateDecimalHours($start_time, $end_time)
{
    $start_timestamp = strtotime($start_time);
    $end_timestamp = strtotime($end_time);

    $diff_seconds = $end_timestamp - $start_timestamp;

    $hours = $diff_seconds / 3600;

    return round($hours, 2);
}

try {
    $query = "SELECT * FROM overtime 
          WHERE employee_id = :employee_id 
          AND overtime_date BETWEEN :start_date AND :end_date 
          AND status = 'Approved'";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'employee_id' => $employee_id,
        'start_date' => $start_date,
        'end_date' => $end_date,
    ]);

    $Records = $stmt->fetchAll();

    $totalHours = 0.00;

    foreach ($Records as $record) {
        if (isset($record['start_time'], $record['end_time'])) {

            $totalHours += calculateDecimalHours($record['start_time'], $record['end_time']);

        }
    }

    $result = [
        'count' => count($Records),
        // 'records' => $Records,
        'totalHours' => $totalHours
    ];
    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>