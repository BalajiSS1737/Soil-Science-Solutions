<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
/** @var PDO $pdo */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Capture form variables matching the HTML input attribute names
$identity = trim($_POST['login_identity'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($identity) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Please provide both identification inputs.']);
    exit;
}

try {
    // 2. Query handles matching user accounts via either direct username OR email strings
    $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND password = ? LIMIT 1");
    $stmt->execute([$identity, $identity, $password]);
    $user = $stmt->fetch();

    if ($user) {
        // Enforce boundary rule: Block administration profiles from accessing this vendor portal
        if (strtolower($user['role']) === 'admin') {
            echo json_encode(['status' => 'error', 'message' => 'Access Denied. System Administrators must use terminal portal roots.']);
            exit;
        }

        // Wipe historical cache fingerprints (Wipes out dealeralan data completely)
        session_unset();

        // Instantiate fresh, isolated user arrays
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = strtolower($user['role']);

        // 3. Map destination pathways matching your folder chart capitalizations exactly
        $redirect = '';
        if ($_SESSION['role'] === 'farmer') {
            $redirect = 'Farmer/dashboard.html';
        } elseif ($_SESSION['role'] === 'dealer') {
            $redirect = 'Dealer/dashboard.html';
        }

        echo json_encode([
            'status' => 'success',
            'redirect' => $redirect
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Username/Email or Password.']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database exception node fault: ' . $e->getMessage()]);
}
// Standardize the role string to lowercase for safe matching
$user_role = strtolower($user['role'] ?? ''); 

if ($user_role === 'farmer') {
    $redirect = 'Farmer/farmer-dashboard.php';
} elseif ($user_role === 'dealer') {
    $redirect = 'Dealer/dealer-dashboard.php';
} elseif ($user_role === 'admin') {
    // 🎯 Make sure this directory name matches your physical folder exactly (e.g., Admin/ or admin/)
    $redirect = 'Admin/dashboard.html'; 
} else {
    // If it falls through, let's see what the database actually returned
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized role descriptor: ' . $user['role']]);
    exit;
}