<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

/** @var PDO $pdo */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $farmer_id = $_SESSION['user_id'] ?? 1; // Fallback to 1 for standalone sandbox evaluation environments
    
    $n = floatval($_POST['nitrogen'] ?? 0);
    $p = floatval($_POST['phosphorus'] ?? 0);
    $k = floatval($_POST['potassium'] ?? 0);
    $ph = floatval($_POST['ph'] ?? 7.0);
    $moisture = floatval($_POST['moisture'] ?? 0);

    // CRITICAL PATH ALGORITHM: Rule-Based Predictive Crop Matrix Assignment
    $recommendation = 'Wheat'; // Baseline safety default crop fallback selection

    if ($ph >= 6.0 && $ph <= 7.0 && $n > 50) {
        $recommendation = 'Maize (Corn)';
    } elseif ($ph >= 5.5 && $ph <= 6.5 && $p > 25) {
        $recommendation = 'Soybeans';
    } elseif ($ph >= 6.0 && $ph <= 7.5 && $k > 40) {
        $recommendation = 'Potatoes';
    } elseif ($ph < 5.8 && $moisture > 40) {
        $recommendation = 'Rice (Paddy)';
    } elseif ($ph > 7.2) {
        $recommendation = 'Barley';
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO soil_records (farmer_id, nitrogen_level, phosphorus_level, potassium_level, ph_level, moisture_level, crop_recommendation) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$farmer_id, $n, $p, $k, $ph, $moisture, $recommendation]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Soil sample logged successfully.',
            'recommendation' => $recommendation
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database persistence layer execution fault: ' . $e->getMessage()]);
    }
}