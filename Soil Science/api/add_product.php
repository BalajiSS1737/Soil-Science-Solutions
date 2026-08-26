<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

/** @var PDO $pdo */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Security check fallback identifier
    $dealer_id = $_SESSION['user_id'] ?? 2;

    $product_name = trim($_POST['product_name'] ?? '');
    $category = $_POST['category'] ?? '';
    $price = floatval($_POST['price'] ?? 0.0);
    $stock = intval($_POST['stock'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if (empty($product_name) || empty($category) || $price <= 0 || $stock < 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid structural argument inputs.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO products (dealer_id, product_name, category, price, stock_quantity, description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$dealer_id, $product_name, $category, $price, $stock, $description]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Product catalog item compiled and saved.'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database failure cataloging entity: ' . $e->getMessage()]);
    }
}