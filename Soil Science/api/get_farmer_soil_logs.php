<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

// 1. Start or resume session to retrieve user identity
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$farmer_id = $_SESSION['user_id'] ?? null;

// 2. Validate Authentication
if (!$farmer_id) {
    echo json_encode(["status" => "error", "message" => "Unauthorized: No active session context."]);
    exit;
}

try {
    // 3. Query logs filtered strictly by the authenticated farmer_id
    // We use a prepared statement to prevent SQL injection
    $sql = "SELECT id, soil_type, ph_level, moisture_level, crop_recommendation, analysis_date 
            FROM soil_logs 
            WHERE farmer_id = ? 
            ORDER BY analysis_date DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$farmer_id]);
    
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 4. Return the telemetry block
    echo json_encode([
        "status" => "success",
        "count" => count($logs),
        "logs" => $logs
    ]);

} catch (PDOException $e) {
    // 5. Catch DB errors and return as JSON for frontend debugging
    echo json_encode([
        "status" => "error", 
        "message" => "Database Query Fault: " . $e->getMessage()
    ]);
}