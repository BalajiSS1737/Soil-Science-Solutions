<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

/** @var PDO $pdo */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? 1;

try {
    // 1. Fetch the latest recorded chemistry log for this user session profile
    $stmt = $pdo->prepare("SELECT * FROM soil_records WHERE farmer_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $record = $stmt->fetch();

    if (!$record) {
        echo json_encode(['status' => 'empty']);
        exit;
    }

    $n = floatval($record['nitrogen_level']);
    $p = floatval($record['phosphorus_level']);
    $k = floatval($record['potassium_level']);
    $ph = floatval($record['ph_level']);
    $moisture = floatval($record['moisture_level']);

    // 2. Compute pH soil amendments
    $amendment = "Your soil pH balance falls within optimal standard threshold ranges. No immediate mineral lime or elemental sulfur treatments required.";
    if ($ph < 6.0) {
        $amendment = "Highly Acidic Profile detected. Apply agricultural limestone (calcium carbonate) at approximately 50 lbs per 1,000 sq ft to buffer acidity and restore root absorption capabilities.";
    } elseif ($ph > 7.5) {
        $amendment = "Alkaline/Basic Profile detected. Incorporate granular elemental sulfur or aluminum sulfate compounds to drop the pH index into neutral bands.";
    }

    // 3. Evaluate macronutrient balances & build fertilization action blueprints
    $fertilizers = [];
    
    // Nitrogen evaluation
    if ($n < 40.0) {
        $fertilizers[] = [
            'element' => 'NITROGEN',
            'action' => 'Apply High-Ratio Urea or Ammonium Nitrate (46-0-0)',
            'rationale' => 'Current concentration levels indicate extreme deficiency. Immediate nitrogen supplementation is required to stimulate vegetative canopy growth.'
        ];
    } else {
        $fertilizers[] = [
            'element' => 'NITROGEN',
            'action' => 'Maintain Standard Green Manure Mulching Cycles',
            'rationale' => 'Nitrogen levels are stabilized. Avoid over-application to prevent structural cell wall elongation weaknesses.'
        ];
    }

    // Phosphorus evaluation
    if ($p < 30.0) {
        $fertilizers[] = [
            'element' => 'PHOSPHORUS',
            'action' => 'Incorporate Triple Superphosphate (TSP) or Bone Meal mixes',
            'rationale' => 'Deficient phosphorus reserves will restrict primary seedling cell root networks. Prioritize early soil incorporation.'
        ];
    } else {
        $fertilizers[] = [
            'element' => 'PHOSPHORUS',
            'action' => 'No active phosphate adjustment needed',
            'rationale' => 'Phosphorus concentrations match standard target profiles for root framework maintenance.'
        ];
    }

    // Potassium evaluation
    if ($k < 50.0) {
        $fertilizers[] = [
            'element' => 'POTASSIUM',
            'action' => 'Top-dress with Muriate of Potash (Potassium Chloride)',
            'rationale' => 'Low potassium levels lower crop resistance to cold weather and drought stress. Essential for fruit and grain weight development.'
        ];
    }

    // 4. Algorithmic Prediction Classifier Map Matching Node Logic
    // Evaluates soil properties against real crop profiles
    if ($ph >= 6.0 && $ph <= 7.0 && $n >= 50 && $p >= 40) {
        $crop = "Hybrid Sweet Corn / Maize";
    } elseif ($ph >= 5.5 && $ph <= 6.5 && $moisture >= 60) {
        $crop = "Lowland Wet Paddy Rice";
    } elseif ($ph >= 6.0 && $ph <= 7.5 && $k >= 60) {
        $crop = "Premium Russet Potatoes / Tubers";
    } elseif ($ph >= 5.0 && $ph <= 6.0 && $n < 40) {
        $crop = "High-Yield Groundnut / Legumes";
    } else {
        $crop = "Hard Red Winter Wheat grains";
    }

    echo json_encode([
        'status' => 'success',
        'metrics' => [
            'n' => $n, 'p' => $p, 'k' => $k, 'ph' => $ph, 'moisture' => $moisture
        ],
        'prediction' => [
            'crop' => $crop,
            'amendment' => $amendment,
            'fertilizers' => $fertilizers
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Engine compilation fatal failure: ' . $e->getMessage()]);
}