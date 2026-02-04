<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Start output buffering to catch any errors
ob_start();

try {
    session_start();
    
    // Check if POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Get POST data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $upi = isset($_POST['upi']) ? trim($_POST['upi']) : '';
    $clickId = isset($_POST['click_id']) ? trim($_POST['click_id']) : '';
    $screenshot = isset($_POST['screenshot']) ? trim($_POST['screenshot']) : '';
    
    // Validate
    if (empty($name) || empty($upi)) {
        throw new Exception('Name and UPI are required');
    }
    
    // Validate UPI format
    if (!preg_match('/^[\w.-]+@[\w.-]+$/', $upi)) {
        throw new Exception('Invalid UPI ID format');
    }
    
    // Connect to database
    require_once '../config/database.php';
    
    if ($conn->connect_error) {
        throw new Exception('Database connection failed');
    }
    
    // Insert application
    $stmt = $conn->prepare("INSERT INTO applications (name, upi_id, click_id, screenshot_path, risk_score, status, ai_verified) VALUES (?, ?, ?, ?, 50, 'pending', 1)");
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("ssss", $name, $upi, $clickId, $screenshot);
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $applicationId = $conn->insert_id;
    
    // Clear any output
    ob_end_clean();
    
    // Send success response
    echo json_encode([
        'success' => true,
        'message' => 'Application submitted successfully',
        'application_id' => $applicationId,
        'status' => 'pending'
    ]);
    
} catch (Exception $e) {
    // Clear any output
    ob_end_clean();
    
    // Send error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
