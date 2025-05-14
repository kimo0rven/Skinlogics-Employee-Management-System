<?php

include 'database.php';
header('Content-Type: application/json');

try {
    $query = "SELECT * FROM rates";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $rates = $stmt->fetchAll();

    echo json_encode($rates[0]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>