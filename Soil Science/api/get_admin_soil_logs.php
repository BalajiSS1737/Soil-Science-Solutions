<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
/** @var PDO $pdo */

session_start();

// 1. SECURITY: Explicitly check for Admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Forbidden: Admin privileges required."]);
    exit;
}

try {
    // 2. QUERY: The JOIN is correct. 
    // Just ensure the 'id' column names don't conflict. 
    // Using aliases (l.id as log_id, u.username) is safer.
    $sql = "SELECT l.id as log_id, l.soil_type, l.ph_level, l.moisture_level, 
                   l.crop_recommendation, l.analysis_date, u.username 
            FROM soil_logs l 
            JOIN users u ON l.farmer_id = u.id 
            ORDER BY l.analysis_date DESC";
            
    $stmt = $pdo->query($sql);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(["status" => "success", "logs" => $logs]);
} catch (PDOException $e) {
    // 3. DEBUGGING: Log the error internally and return a clean message
    error_log("Database Error in Admin API: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Internal Database Error"]);
}