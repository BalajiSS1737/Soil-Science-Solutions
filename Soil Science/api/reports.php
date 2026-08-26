<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

/** @var PDO $pdo */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Check: Optional block to restrict access to administrators
// enforceRole('admin');

try {
    // 1. Calculate total registered farmer accounts
    $farmerQuery = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'farmer'");
    $totalFarmers = $farmerQuery->fetchColumn();

    // 2. Calculate total active vendor supplier nodes
    $dealerQuery = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'dealer'");
    $totalDealers = $dealerQuery->fetchColumn();

    // 3. Calculate total analyzed soil telemetry records
    $recordsQuery = $pdo->query("SELECT COUNT(*) FROM soil_records");
    $totalRecords = $recordsQuery->fetchColumn();

    // 4. Compute global geographic pH averages
    $phQuery = $pdo->query("SELECT AVG(ph_level) FROM soil_records");
    $averagePh = $phQuery->fetchColumn();

    // Fallback normalization logic if database logs are currently empty
    if (is_null($averagePh)) {
        $averagePh = 0.0;
    }

    // Return pristine data array back to frontend canvas engine
    echo json_encode([
        'status' => 'success',
        'summary' => [
            'farmers' => intval($totalFarmers),
            'dealers' => intval($totalDealers),
            'avg_ph' => floatval($averagePh),
            'total_records' => intval($totalRecords)
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Data pipeline telemetry compile failure: ' . $e->getMessage()
    ]);
}