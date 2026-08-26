<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

/** @var PDO $pdo */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$dealer_id = $_SESSION['user_id'] ?? 2; // Simulation testing authorization pointer code fallback

try {
    $stmt = $pdo->prepare("
        SELECT dr.*, u.username as farmer_name, p.product_name 
        FROM dealer_requests dr
        JOIN users u ON dr.farmer_id = u.id
        LEFT JOIN products p ON dr.product_id = p.id
        WHERE dr.dealer_id = ?
        ORDER BY dr.id DESC
    ");
    $stmt->execute([$dealer_id]);
    $requests = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'requests' => $requests
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Pipeline extraction crash error: ' . $e->getMessage()]);
}