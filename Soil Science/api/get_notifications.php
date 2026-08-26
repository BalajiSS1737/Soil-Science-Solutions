<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

/** @var PDO $pdo */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? 1;

try {
    // 1. Fetch all notifications for the active user session
    $stmt = $pdo->prepare("SELECT id, title, message, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
    $stmt->execute([$user_id]);
    $alerts = $stmt->fetchAll();

    // 2. Mark retrieved alerts as read automatically
    $updateStmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
    $updateStmt->execute([$user_id]);

    echo json_encode([
        'status' => 'success',
        'notifications' => $alerts
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Notification dispatch engine fault: ' . $e->getMessage()]);
}