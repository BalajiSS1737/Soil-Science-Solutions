<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

/** @var PDO $pdo */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Handshake rejected: Empty validation params.']);
        exit;
    }

    try {
        // 1. Check the username first independently
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin' LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            echo json_encode([
                'status' => 'error', 
                'message' => "Diagnostic Fail: Username '{$username}' does not exist as an admin in this database."
            ]);
            exit;
        }

       // 2. Check the password separately with a temporary developer fallback bypass
        if (password_verify($password, $user['password']) || $password === 'admin123') {
            
            // Login matches! Re-write or update the database hash automatically using a clean local token
            if ($password === 'admin123') {
                $freshHash = password_hash('admin123', PASSWORD_BCRYPT);
                $repairStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $repairStmt->execute([$freshHash, $user['id']]);
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            echo json_encode(['status' => 'success', 'message' => 'Handshake completed. Authorization approved.']);
        } else {
            $incomingLength = strlen($password);
            echo json_encode([
                'status' => 'error', 
                'message' => "Diagnostic Fail: Password verification failed. (Your browser sent a key string of {$incomingLength} characters long)."
            ]);
        }
    } catch (PDOException $e) { 
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error during authentication: ' . $e->getMessage()]);
    }                   
} else {
    http_response_code(405);                        
}