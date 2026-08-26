<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

/** @var PDO $pdo */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fallback for testing: If not logged in via session, use ID 1
$farmer_id = $_SESSION['user_id'] ?? 1; 
$username = $_SESSION['username'] ?? 'Active Farmer';

try {
    // Fetch recent analytics records
    $recordStmt = $pdo->prepare("SELECT id, analysis_date, nitrogen_level, phosphorus_level, potassium_level, ph_level, moisture_level, crop_recommendation FROM soil_records WHERE farmer_id = ? ORDER BY analysis_date DESC LIMIT 5");
    $recordStmt->execute([$farmer_id]);
    $records = $recordStmt->fetchAll();

    // Default metrics if database is blank
    $latestPh = 'N/A';
    if (!empty($records)) {
        $latestPh = $records[0]['ph_level'];
    }
    
    $cropStmt = $pdo->prepare("SELECT COUNT(DISTINCT crop_recommendation) AS active_crops FROM soil_records WHERE farmer_id = ? AND crop_recommendation IS NOT NULL AND crop_recommendation != ''");
    $cropStmt->execute([$farmer_id]);
    $activeCropsCount = $cropStmt->fetch()['active_crops'] ?? 0;

    $orderStmt = $pdo->prepare("SELECT COUNT(*) AS pending_orders FROM dealer_requests WHERE farmer_id = ? AND status = 'pending'");
    $orderStmt->execute([$farmer_id]);
    $pendingOrdersCount = $orderStmt->fetch()['pending_orders'] ?? 0;

    echo json_encode([
        'status' => 'success',
        'username' => $username,
        'metrics' => [
            'recent_ph' => $latestPh,
            'active_crops' => $activeCropsCount,
            'pending_orders' => $pendingOrdersCount
        ],
        'records' => $records
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}