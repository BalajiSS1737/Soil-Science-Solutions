<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

/** @var PDO $pdo */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security verification fallback: Default to Dealer ID 2 (generated during product seeding)
$dealer_id = $_SESSION['user_id'] ?? 2;
$username = $_SESSION['username'] ?? 'Apex Agricultural Supplies';

try {
    // 1. Gather pipeline metric numbers
    $pStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM dealer_requests WHERE dealer_id = ? AND status = 'pending'");
    $pStmt->execute([$dealer_id]);
    $pending = $pStmt->fetch()['cnt'] ?? 0;

    $aStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM dealer_requests WHERE dealer_id = ? AND status = 'approved'");
    $aStmt->execute([$dealer_id]);
    $approved = $aStmt->fetch()['cnt'] ?? 0;

    $prodStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM products WHERE dealer_id = ?");
    $prodStmt->execute([$dealer_id]);
    $totalProducts = $prodStmt->fetch()['cnt'] ?? 0;

    // 2. Fetch full operational data profiles for incoming requests
    $reqStmt = $pdo->prepare("
        SELECT r.id, r.message, r.status, r.created_at, p.product_name, u.username as farmer_name
        FROM dealer_requests r
        JOIN products p ON r.product_id = p.id
        JOIN users u ON r.farmer_id = u.id
        WHERE r.dealer_id = ?
        ORDER BY r.created_at DESC
    ");
    $reqStmt->execute([$dealer_id]);
    $requests = $reqStmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'username' => $username,
        'metrics' => [
            'pending' => $pending,
            'approved' => $approved,
            'total_products' => $totalProducts
        ],
        'requests' => $requests
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Dealer database retrieval crash: ' . $e->getMessage()]);
}