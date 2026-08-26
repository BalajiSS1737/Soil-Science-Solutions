<?php
header('Content-Type: text/html; charset=utf-8');
require_once '../includes/db_connect.php';

/** @var PDO $pdo */

echo "<body style='background:#F5F7FA; font-family:sans-serif; color:#333; padding:40px;'>";
echo "<div style='background:#FFF; padding:30px; border-radius:8px; max-width:700px; margin:0 auto; box-shadow:0 4px 12px rgba(0,0,0,0.05);'>";
echo "<h2 style='color:#2E7D32; margin-top:0;'>🛡️ AgriPulse System Auth Debugger</h2>";
echo "<hr style='border:0; border-top:1px solid #eee; margin-bottom:20px;'>";

try {
    // 1. Check if the user exists at all
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'system_admin'");
    $stmt->execute();
    $user = $stmt->fetch();

    if (!$user) {
        echo "<p style='color:#d32f2f;'><strong>❌ ERROR:</strong> No account named <code>system_admin</code> exists in your <code>users</code> table right now.</p>";
        echo "<p><em>Fix: Go to phpMyAdmin and re-run the INSERT query.</em></p>";
        exit;
    }

    echo "<p style='color:#2E7D32;'><strong>✅ ROW FOUND:</strong> User <code>system_admin</code> exists in your table.</p>";
    echo "<ul>";
    echo "<li><strong>Target Role:</strong> " . htmlspecialchars($user['role']) . "</li>";
    echo "<li><strong>Stored Hash:</strong> <code style='background:#f5f5f5; padding:2px 6px; border-radius:4px;'>" . htmlspecialchars($user['password']) . "</code></li>";
    echo "<li><strong>Hash Character Length:</strong> " . strlen($user['password']) . " characters</li>";
    echo "</ul>";

    // 2. Test password verification dynamically right here
    $testPassword = 'admin123';
    echo "<h4 style='margin-bottom:5px;'>Running Verification Test...</h4>";
    
    if (password_verify($testPassword, $user['password'])) {
        echo "<p style='background:#e8f5e9; color:#2e7d32; padding:10px; border-radius:4px;'><strong>✅ MATCH SUCCESSFUL!</strong> Your PHP engine successfully matched 'admin123' to the database hash. If your login page still fails, the problem is your HTML form input names or paths.</p>";
    } else {
        echo "<p style='background:#ffebee; color:#c62828; padding:10px; border-radius:4px;'><strong>❌ MATCH FAILED!</strong> The string 'admin123' does not match the hash in your database. This proves the hash was modified or cut off during copy-pasting.</p>";
        
        // 3. Generate a 100% native hash specifically for their computer
        $nativeHash = password_hash('admin123', PASSWORD_BCRYPT);
        echo "<h4 style='margin-top:25px; margin-bottom:5px; color:#2E7D32;'>Repairing the Hash:</h4>";
        echo "<p>Here is a fresh cryptographic token generated directly by your local XAMPP PHP engine. Run the SQL statement below to apply it:</p>";
        echo "<textarea style='width:100%; height:40px; font-family:monospace; padding:10px; background:#222; color:#4CAF50; border:0; border-radius:4px; margin-bottom:15px;' readonly>UPDATE users SET password = '{$nativeHash}' WHERE username = 'system_admin';</textarea>";
    }

} catch (Exception $e) {
    echo "<p style='color:red;'><strong>Database Error:</strong> " . $e->getMessage() . "</p>";
}

echo "</div></body>";