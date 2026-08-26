<?php
require_once '../includes/db_connect.php';

/** @var PDO $pdo */ 
// ^ This single line tells VS Code: "Trust me, $pdo exists and it is a PDO object."

if (isset($pdo)) {
    echo "Database connection is working perfectly!";
} else {
    echo "Connection failed: $pdo is not defined.";
}
?>