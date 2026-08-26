<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$farmer_id = $_SESSION['user_id'] ?? null;
if (!$farmer_id || ($_SESSION['role'] !== 'farmer' && $_SESSION['role'] !== 'admin')) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}

// 1. Sanitize Inputs
$data = [
    "soil_type"   => (float)$_POST['soil_type'],
    "ph"          => (float)$_POST['ph'],
    "moisture"    => (float)$_POST['moisture'],
    "temperature" => (float)$_POST['temperature'],
    "rainfall"    => (float)$_POST['rainfall']
];

$json_payload = json_encode($data);

// 2. Define absolute paths (CRITICAL: Replace YourPCName)
$python_exe = "C:\\Users\\balaj\\AppData\\Local\\Programs\\Python\\Python314\\python.exe";
$script_path = __DIR__ . "\\predict.py";

// 3. Pipe Data to Python
$descriptorspec = [
    0 => ["pipe", "r"], // stdin
    1 => ["pipe", "w"], // stdout
    2 => ["pipe", "w"]  // stderr
];

// Execute using the full path with quotes to handle potential spaces
$cmd = sprintf('"%s" "%s"', $python_exe, $script_path);
$process = proc_open($cmd, $descriptorspec, $pipes);

if (is_resource($process)) {
    fwrite($pipes[0], $json_payload);
    fclose($pipes[0]);

    $output = stream_get_contents($pipes[1]);
    $errors = stream_get_contents($pipes[2]);
    
    fclose($pipes[1]);
    fclose($pipes[2]);
    $return_value = proc_close($process);

    if ($return_value !== 0) {
        echo json_encode(["status" => "error", "message" => "Python runtime error: " . $errors]);
        exit;
    }
} else {
    echo json_encode(["status" => "error", "message" => "Could not execute Python process."]);
    exit;
}

// 4. Parse Python Result
$result = json_decode($output, true);
if (!$result || $result['status'] !== 'success') {
    echo json_encode(["status" => "error", "message" => $result['message'] ?? "Unknown failure"]);
    exit;
}

// 5. Database Save
try {
    $stmt = $pdo->prepare("INSERT INTO soil_logs (farmer_id, soil_type, ph_level, moisture_level, crop_recommendation, analysis_date) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$farmer_id, $data['soil_type'], $data['ph'], $data['moisture'], $result['crop_recommendation']]);
    echo json_encode(["status" => "success", "crop" => $result['crop_recommendation']]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}