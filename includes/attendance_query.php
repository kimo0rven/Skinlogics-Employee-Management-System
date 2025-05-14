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

try {
    $query = "SELECT * FROM attendance WHERE employee_id = :employee_id AND date_created BETWEEN :start_date AND :end_date";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'employee_id' => $employee_id,
        'start_date' => $start_date,
        'end_date' => $end_date,
    ]);

    $attendanceRecords = $stmt->fetchAll();

    $totalWorkedHours = 0.00;

    foreach ($attendanceRecords as $record) {
        if (isset($record['worked_hours']) && is_numeric($record['worked_hours'])) {
            $totalWorkedHours += floatval($record['worked_hours']);
        }
    }

    $result = [
        'count' => count($attendanceRecords),
        'records' => $attendanceRecords,
        'totalHours' => $totalWorkedHours
    ];
    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>