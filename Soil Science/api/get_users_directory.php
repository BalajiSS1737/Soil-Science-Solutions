<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

/** @var PDO $pdo */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Read list profiles, omitting high-security password hash strings from output structures
    $stmt = $pdo->query("SELECT id, username, email, role, created_at FROM users ORDER BY role ASC, id DESC");
    $users = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'users' => $users
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Identity database matrix table selection failed: ' . $e->getMessage()
    ]);
}