<?php

include 'database.php';
header('Content-Type: application/json');

// Retrieve the search query
$q = isset($_GET['q']) ? $_GET['q'] : '';

try {
    $sql = "SELECT 
                e.*,
                j.job_id,
                j.job_name,
                j.salary,
                d.department_name
            FROM employee e
            INNER JOIN job j ON e.job_id = j.job_id
            INNER JOIN department d ON j.department_id = d.department_id;
            ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("" . $e->getMessage());
}

$results = [];

if ($q !== '') {
    foreach ($employees as $employee) {
        if (stripos($employee['first_name'], $q) !== false || stripos($employee['last_name'], $q) !== false) {
            // Combine first and last names
            $employee['full_name'] = $employee['first_name'] . ' ' . $employee['last_name'];
            // Optionally, create a display field that also shows the employee ID
            $employee['display_text'] = $employee['full_name'] . " (ID: " . $employee['employee_id'] . ")";
            $results[] = $employee;
        }
    }
}

echo json_encode($results);
?>