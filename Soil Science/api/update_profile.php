<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

/** @var PDO $pdo */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? 1;
    
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
   try {
        $pdo->beginTransaction();

        // 1. Check if this user already has a profile row initialized
        $checkProfile = $pdo->prepare("SELECT COUNT(*) FROM profiles WHERE user_id = ?");
        $checkProfile->execute([$user_id]);
        $profileExists = $checkProfile->fetchColumn() > 0;

        if ($profileExists) {
            // Row exists, update it normally
            $stmt = $pdo->prepare("UPDATE profiles SET full_name = ?, phone = ?, address = ? WHERE user_id = ?");
            $stmt->execute([$full_name, $phone, $address, $user_id]);
        } else {
            // Legacy user fallback: Insert a fresh row for them on the fly
            $stmt = $pdo->prepare("INSERT INTO profiles (user_id, full_name, phone, address) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $full_name, $phone, $address]);
        }
// 2. Process the binary image stream variables with strict error catching
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['name'] !== '') {
            
            // Check if XAMPP intercepted and broke the file upload stream
            if ($_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
                switch ($_FILES['profile_photo']['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                        throw new Exception("Server Reject: The file exceeds the 'upload_max_filesize' directive in your XAMPP php.ini file.");
                    case UPLOAD_ERR_FORM_SIZE:
                        throw new Exception("Server Reject: The file exceeds the MAX_FILE_SIZE directive specified in the HTML form.");
                    case UPLOAD_ERR_PARTIAL:
                        throw new Exception("Network Fault: The file was only partially uploaded.");
                    case UPLOAD_ERR_NO_FILE:
                        // No file selected, safe to ignore
                        break;
                    default:
                        throw new Exception("System Error: File upload failed with system code " . $_FILES['profile_photo']['error']);
                }
            }

            // If we clear the error checks, assign parameters
            $fileTmpPath = $_FILES['profile_photo']['tmp_name'];
            $fileName = $_FILES['profile_photo']['name'];
            $fileSize = $_FILES['profile_photo']['size'];
            
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($fileExtension, $allowedExtensions)) {
                if ($fileSize <= 5 * 1024 * 1024) { // 5MB Maximum Threshold Limit
                    
                    $newFileName = md5(time() . $user_id) . '.' . $fileExtension;
                    $uploadFileDir = '../uploads/profile_photos/';
                    $dest_path = $uploadFileDir . $newFileName;
                    
                    if(!is_dir($uploadFileDir)){
                        if (!mkdir($uploadFileDir, 0755, true)) {
                            throw new Exception("Directory Error: PHP lacks permissions to create the '../uploads/profile_photos/' folder.");
                        }
                    }

                    if(move_uploaded_file($fileTmpPath, $dest_path)) {
                        $dbStorePath = 'uploads/profile_photos/' . $newFileName;
                        
                        $imgUpdateStmt = $pdo->prepare("UPDATE profiles SET photo_path = ? WHERE user_id = ?");
                        $imgUpdateStmt->execute([$dbStorePath, $user_id]);
                    } else {
                        throw new Exception("Write Error: PHP failed to move the file from temporary cache to target directory.");
                    }
                } else {
                    throw new Exception("File capacity ceiling warning: Limit file sizing to under 5MB.");
                }
            } else {
                throw new Exception("Unsupported file formatting mime types blocked.");
            }
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Account attributes synchronized perfectly.']);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

?>