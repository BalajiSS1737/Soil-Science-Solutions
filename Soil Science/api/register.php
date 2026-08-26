<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
/** @var PDO $pdo */

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$role = trim($_POST['role'] ?? '');

if (empty($username) || empty($email) || empty($password) || empty($role)) {
    echo json_encode(['status' => 'error', 'message' => 'All structural setup criteria parameters are required.']);
    exit;
}

try {
    // 1. Verify availability matrix indices to prevent registration collisions
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
    $checkStmt->execute([$username, $email]);
    
    if ($checkStmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Account conflict: Username or Email is already registered.']);
        exit;
    }

    // 2. Commit plain-text record row into database configuration layout
    $insertStmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $insertStmt->execute([$username, $email, $password, strtolower($role)]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Membership row array allocated successfully.'
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Critical registration log exception: ' . $e->getMessage()]);
}