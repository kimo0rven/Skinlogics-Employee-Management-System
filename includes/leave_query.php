<?php

include 'database.php';
header('Content-Type: application/json');

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

function calculateLeaveDays($start_date, $end_date)
{
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);

    $interval = $start->diff($end);

    return $interval->days + 1;
}

try {
    $query = "SELECT * FROM leave_request 
          WHERE employee_id = :employee_id 
          AND status = 'Approved'
          AND (
              (start_date BETWEEN :start_date AND :end_date)
              OR (end_date BETWEEN :start_date AND :end_date)
              OR (:start_date BETWEEN start_date AND end_date)
              OR (:end_date BETWEEN start_date AND end_date)
          )";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'employee_id' => $employee_id,
        'start_date' => $start_date,
        'end_date' => $end_date,
    ]);

    $Records = $stmt->fetchAll();

    $totalDays = 0;

    foreach ($Records as $record) {
        if (isset($record['start_date'], $record['end_date'])) {
            $totalDays += calculateLeaveDays($record['start_date'], $record['end_date']);
        }
    }

    $result = [
        'count' => count($Records),
        // 'records' => $Records,
        'totalDays' => $totalDays
    ];
    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>