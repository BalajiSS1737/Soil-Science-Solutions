<?php
// Ensure a session is only started if one doesn't actively exist
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Security Gate: Protects backend routes from unauthenticated requests
 */
function enforceAuthentication() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Session expired or invalid.']);
        exit;
    }
}

/**
 * Role Gate: Ensures users have the correct access privileges
 */
function enforceRole($allowedRole) {
    enforceAuthentication();
    if ($_SESSION['role'] !== $allowedRole) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Forbidden: You do not have permission to access this node.']);
        exit;
    }
}