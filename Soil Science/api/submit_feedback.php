<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

/** @var PDO $pdo */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? 1; // Fallback validation identity pointer
    
    $rating = intval($_POST['rating'] ?? 0);
    $comments = trim($_POST['comments'] ?? '');

    if ($rating < 1 || $rating > 5 || empty($comments)) {
        echo json_encode(['status' => 'error', 'message' => 'Malformed parameter payloads context data rows.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO feedback (user_id, rating, comments) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $rating, $comments]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Evaluation matrix record successfully logged.'
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database persistence execution trace crash: ' . $e->getMessage()]);
    }
}