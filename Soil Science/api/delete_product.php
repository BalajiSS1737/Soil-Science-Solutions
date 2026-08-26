<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

/** @var PDO $pdo */
    
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);

    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing transaction target locator token identifier.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Target resource wiped successfully.'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database dropped action execution error: ' . $e->getMessage()]);
    }
}