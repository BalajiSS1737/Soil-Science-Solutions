<?php
// Database Configuration Vectors
$host = '127.0.0.1'; // or 'localhost'
$db   = 'agri_pulse'; // Make sure this matches your phpMyAdmin DB name exactly
$user = 'root';       // Default XAMPP username
$pass = '';           // Default XAMPP password is empty
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // If this triggers, it prints the raw MySQL failure string
     die("Critical Node Handshake Failure: " . $e->getMessage());
}