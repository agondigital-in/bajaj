<?php
header('Content-Type: application/json');
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Generate Click ID
    $clickId = generateClickId();
    
    // Store in session
    $_SESSION['click_id'] = $clickId;
    $_SESSION['apply_timestamp'] = time();
    
    // Generate tracking URL
    $trackingUrl = generateTrackingUrl($clickId);
    
    // Log the click
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    
    $stmt = $conn->prepare("INSERT INTO click_tracking (click_id, tracking_url, ip_address, user_agent) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $clickId, $trackingUrl, $ip, $userAgent);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'click_id' => $clickId,
        'redirect_url' => $trackingUrl
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
