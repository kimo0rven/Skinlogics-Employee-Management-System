<?php
ob_start();
include 'database.php';
session_start();

$submissionResult = ['success' => false, 'message' => '', 'error' => '', 'redirect' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['start_time'], $_POST['end_time'], $_POST['ot_type'], $_POST['reason'])) {
        try {
            $sql = 'INSERT INTO overtime (start_time, end_time, ot_type, ot_reason, overtime_date, employee_id)
                    VALUES (:start_time, :end_time, :ot_type, :ot_reason, NOW(), :employee_id)';
            $stmt_overtime = $pdo->prepare($sql);
            $stmt_overtime->execute([
                ':start_time' => htmlspecialchars($_POST['start_time']),
                ':end_time' => htmlspecialchars($_POST['end_time']),
                ':ot_type' => htmlspecialchars($_POST['ot_type']),
                ':ot_reason' => htmlspecialchars($_POST['reason']),
                ':employee_id' => $_SESSION['employee_id']
            ]);
            $submissionResult['success'] = true;
            $submissionResult['message'] = 'Overtime request submitted successfully!';
            $submissionResult['redirect'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'defaultPage.php';
        } catch (PDOException $e) {
            $submissionResult['error'] = "Database error: " . $e->getMessage();
        }
    } else {
        $submissionResult['error'] = "Missing required form fields.";
    }

    ob_clean();
    header('Content-Type: application/json');
    echo json_encode($submissionResult);

    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    error_reporting(E_ALL);
    exit();
}
?>