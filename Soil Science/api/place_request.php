<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

/** @var PDO $pdo */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Authenticated cross check validation handles 
    $farmer_id = $_SESSION['user_id'] ?? 1; // Testing fallback context environment
    
    $product_id = intval($_POST['product_id'] ?? 0);
    $dealer_id = intval($_POST['dealer_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');

    if (empty($product_id) || empty($dealer_id) || empty($message)) {
        echo json_encode(['status' => 'error', 'message' => 'Incomplete parameter request payloads context.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO dealer_requests (farmer_id, dealer_id, product_id, message, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$farmer_id, $dealer_id, $product_id, $message]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Procurement records dispatched successfully.'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database write error tracking transaction: ' . $e->getMessage()]);
    }
}