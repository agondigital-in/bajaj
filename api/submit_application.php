<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
session_start();

try {
    require_once '../config/database.php';
    require_once '../includes/functions.php';
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Configuration error']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $upi = sanitizeInput($_POST['upi']);
    $clickId = sanitizeInput($_POST['click_id']);
    $screenshot = sanitizeInput($_POST['screenshot']);
    
    // Validate UPI format
    if (!preg_match('/^[\w.-]+@[\w.-]+$/', $upi)) {
        echo json_encode(['success' => false, 'message' => 'Invalid UPI ID format']);
        exit;
    }
    
    // Check for duplicate
    if (checkDuplicateSubmission($upi)) {
        echo json_encode(['success' => false, 'message' => 'Duplicate submission detected']);
        exit;
    }
    
    // Calculate risk score
    $riskData = [
        'upi' => $upi,
        'screenshot_quality' => 75,
        'ai_confidence' => 85
    ];
    $riskScore = calculateRiskScore($riskData);
    
    // Determine status based on risk
    $status = 'pending';
    if ($riskScore > RISK_HIGH) {
        $status = 'rejected';
    } elseif ($riskScore < RISK_LOW) {
        $status = 'approved';
    }
    
    // Insert application
    try {
        $stmt = $conn->prepare("INSERT INTO applications (name, upi_id, click_id, screenshot_path, risk_score, status, ai_verified) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("ssssis", $name, $upi, $clickId, $screenshot, $riskScore, $status);
        
        if ($stmt->execute()) {
            $applicationId = $conn->insert_id;
            
            // Log activity
            logActivity($applicationId, 'application_submitted', "Name: $name, UPI: $upi");
            
            echo json_encode([
                'success' => true,
                'message' => 'Application submitted successfully',
                'application_id' => $applicationId,
                'status' => $status
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
