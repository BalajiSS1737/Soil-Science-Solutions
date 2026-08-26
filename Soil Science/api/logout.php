<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Unset all session memory variables 
$_SESSION = array();

// 2. Clear the session cookie from the browser's cookie jar entirely
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// 3. Destroy the session registration file on the Apache server
session_destroy();

// 4. Eject the user back to your core login page
// Note: Adjusted path out of the api/ folder into your root directory
header("Location: ../login.php"); 
exit;