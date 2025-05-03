<?php
ob_start();
include 'database.php';
session_start();

$response = ['success' => false, 'message' => '', 'error' => '', 'redirect' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['leave_type'], $_POST['start_date'], $_POST['end_date'], $_POST['reason'])) {
        try {
            $sql = "INSERT INTO leave_request (leave_type, start_date, end_date, reason, employee_id, tl_approval, hr_manager_approval, date_created)
                    VALUES (:leave_type, :start_date, :end_date, :reason, :employee_id, :tl_approval, :hr_manager_approval, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':leave_type' => htmlspecialchars($_POST['leave_type']),
                ':start_date' => htmlspecialchars($_POST['start_date']),
                ':end_date' => htmlspecialchars($_POST['end_date']),
                ':reason' => htmlspecialchars($_POST['reason']),
                ':employee_id' => $_SESSION['employee_id'],
                ':tl_approval' => 'Pending',
                ':hr_manager_approval' => 'Pending'
            ]);
            $response['success'] = true;
            $response['message'] = 'Leave request submitted successfully!';
            $response['redirect'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
        } catch (PDOException $e) {
            $response['error'] = "Database error: " . $e->getMessage();
        }
    } else {
        $response['error'] = "Missing required form fields.";
    }
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>