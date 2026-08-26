<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Grab the user ID dynamically from the active session
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access: Session expired or invalid.']);
    exit;
}

try {
    // 2. Fetch profile metrics coupled directly with the user account role
    $stmt = $pdo->prepare("
        SELECT u.username, u.email, u.role, p.full_name, p.phone, p.address 
        FROM users u
        LEFT JOIN profiles p ON u.id = p.user_id
        WHERE u.id = ?
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $accountData = $stmt->fetch();

    if ($accountData) {
        echo json_encode([
            'status' => 'success',
            'profile' => $accountData
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Account configuration entry not found.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database operation error: ' . $e->getMessage()]);
}