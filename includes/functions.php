<?php
// Generate Dynamic Click ID
function generateClickId() {
    $patterns = ['a', 'b', 'aa', 'bb', 'abc', 'xyz', 'aaa', 'bbb'];
    $random = $patterns[array_rand($patterns)];
    $timestamp = substr(time(), -4);
    return $random . $timestamp;
}

// Generate Tracking URL
function generateTrackingUrl($clickId) {
    $baseUrl = "https://www.bajajfinserv.in/webform/v1/emicard/login";
    $params = [
        'source' => 'affiliate',
        'clickid' => $clickId,
        'timestamp' => time()
    ];
    return $baseUrl . '?' . http_build_query($params);
}

// AI Image Optimization
function optimizeImage($imagePath) {
    if (!AI_IMAGE_OPTIMIZATION) return $imagePath;
    
    list($width, $height) = getimagesize($imagePath);
    $maxWidth = 1200;
    $maxHeight = 800;
    
    if ($width > $maxWidth || $height > $maxHeight) {
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = $width * $ratio;
        $newHeight = $height * $ratio;
        
        $optimized = imagecreatetruecolor($newWidth, $newHeight);
        $source = imagecreatefromjpeg($imagePath);
        imagecopyresampled($optimized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        $outputPath = 'uploads/optimized_' . basename($imagePath);
        imagejpeg($optimized, $outputPath, 85);
        
        imagedestroy($optimized);
        imagedestroy($source);
        
        return $outputPath;
    }
    
    return $imagePath;
}

// Sanitize Input
function sanitizeInput($data) {
    global $conn;
    if (!isset($data)) return '';
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    if (isset($conn) && $conn) {
        return $conn->real_escape_string($data);
    }
    return $data;
}

// Calculate Risk Score
function calculateRiskScore($data) {
    $score = 0;
    
    // Check for duplicate submissions
    if (checkDuplicateSubmission($data['upi'])) {
        $score += 40;
    }
    
    // Check screenshot quality
    if ($data['screenshot_quality'] < 50) {
        $score += 30;
    }
    
    // Check AI verification confidence
    if ($data['ai_confidence'] < 70) {
        $score += 20;
    }
    
    // Check UPI format
    if (!preg_match('/^[\w.-]+@[\w.-]+$/', $data['upi'])) {
        $score += 10;
    }
    
    return min($score, 100);
}

// Check Duplicate Submission
function checkDuplicateSubmission($upi) {
    global $conn;
    if (!isset($conn) || !$conn) return false;
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM applications WHERE upi_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    if (!$stmt) return false;
    
    $stmt->bind_param("s", $upi);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'] > 0;
}

// Log Activity
function logActivity($userId, $action, $details) {
    global $conn;
    if (!isset($conn) || !$conn) return false;
    
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    if (!$stmt) return false;
    
    $stmt->bind_param("isss", $userId, $action, $details, $ip);
    return $stmt->execute();
}
?>
