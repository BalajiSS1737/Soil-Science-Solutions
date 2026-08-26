
<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
session_start();

// FIX: Allow Farmers AND Dealers to access this marketplace API
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'farmer' && $_SESSION['role'] !== 'dealer')) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access: You do not have the required role."]);
    exit;
}

try {
    // Fetch products
    $stmt = $pdo->query("SELECT * FROM products");
    $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["status" => "success", "inventory" => $inventory]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error"]);
}