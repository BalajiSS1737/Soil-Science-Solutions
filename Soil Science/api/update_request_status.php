<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

/** @var PDO $pdo */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = intval($_POST['id'] ?? 0);
    $targetStatus = $_POST['status'] ?? '';

    if (empty($request_id) || !in_array($targetStatus, ['approved', 'rejected'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid parameters sent to transaction worker node.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Extract the order profile details
        $stmt = $pdo->prepare("SELECT * FROM dealer_requests WHERE id = ? FOR UPDATE");
        $stmt->execute([$request_id]);
        $order = $stmt->fetch();

        if (!$order) {
            echo json_encode(['status' => 'error', 'message' => 'Target order line item not found in server tables.']);
            exit;
        }

        // 2. Handle warehouse stock deductions for approved items
        if ($targetStatus === 'approved' && !empty($order['product_id'])) {
            // Check current supplier stock counts 
            $pStmt = $pdo->prepare("SELECT stock_quantity, product_name FROM products WHERE id = ?");
            $pStmt->execute([$order['product_id']]);
            $product = $pStmt->fetch();

            if ($product) {
                if ($product['stock_quantity'] < 1) {
                    echo json_encode(['status' => 'error', 'message' => 'Transaction block: Item out of stock in warehouse inventories.']);
                    exit;
                }
                
                // Decrement stock levels by 1 structural unit listing
                $decStmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - 1 WHERE id = ?");
                $decStmt->execute([$order['product_id']]);
                $itemName = $product['product_name'];
            }
        } else {
            $itemName = "Marketplace Materials Entry";
        }

        // 3. Flip status variables on core tracking row arrays
        $updateStmt = $pdo->prepare("UPDATE dealer_requests SET status = ? WHERE id = ?");
        $updateStmt->execute([$targetStatus, $request_id]);

        // 4. AUTOMATED ALERTS INJECTION TERMINAL
        // Instantly generate and dispatch a notification directly to the buying farmer's feed
        $titleMessage = ($targetStatus === 'approved') ? 'Procurement Request Approved' : 'Procurement Request Declined';
        $bodyMessage = ($targetStatus === 'approved') 
            ? "Your raw material delivery request for [{$itemName}] has been reviewed and packed by the vendor." 
            : "The supplier has canceled or deferred your marketplace request for [{$itemName}]. Contact vendor terminal directly.";

        $notifyStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
        $notifyStmt->execute([$order['farmer_id'], $titleMessage, $bodyMessage]);

        $pdo->commit();
        echo json_encode([
            'status' => 'success', 
            'message' => "Order ticket successfully logged as {$targetStatus} and alerting dispatched to farmer channel."
        ]);

    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database dropped structural updates loop: ' . $e->getMessage()]);
    }
}