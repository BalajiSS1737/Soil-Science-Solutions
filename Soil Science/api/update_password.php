<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

/** @var PDO $pdo */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? 1; // Fallback simulation testing identification vector

    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // 1. Enforce completeness validations 
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        echo json_encode(['status' => 'error', 'message' => 'Incomplete parameter request string payloads.']);
        exit;
    }

    if ($new_password !== $confirm_password) {
        echo json_encode(['status' => 'error', 'message' => 'New credential matching string confirmation validation failed.']);
        exit;
    }

    if (strlen($new_password) < 6) {
        echo json_encode(['status' => 'error', 'message' => 'Security floor warning: Password length must be at least 6 characters.']);
        exit;
    }

    try {
        // 2. Fetch the existing cryptographically hashed security string password from the core users table index maps
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $userRecord = $stmt->fetch();

        if (!$userRecord) {
            echo json_encode(['status' => 'error', 'message' => 'Identity profile lookup failure: Reference pointer not found.']);
            exit;
        }

        // 3. Evaluate the provided text string against the stored bcrypt hash model
        if (!password_verify($current_password, $userRecord['password'])) {
            echo json_encode(['status' => 'error', 'message' => 'Security violation: Current password verification token incorrect.']);
            exit;
        }

        // 4. Generate a new secure salt profile key string 
        $newSecureHashStr = password_hash($new_password, PASSWORD_BCRYPT);

        // 5. Save the updated hash to the database
        $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $updateStmt->execute([$newSecureHashStr, $user_id]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Security authorization parameters committed perfectly.'
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database layer failure altering system assets: ' . $e->getMessage()]);
    }
}