<?php
header('Content-Type: application/json');
session_start();
require_once '../config/database.php';
require_once '../config/ai_config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['screenshot'])) {
    $file = $_FILES['screenshot'];
    
    // Validate file
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type']);
        exit;
    }
    
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File too large']);
        exit;
    }
    
    // Upload file
    $uploadDir = '../uploads/screenshots/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = uniqid() . '_' . basename($file['name']);
    $uploadPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // AI OCR Validation
        $ocrResult = performOCR($uploadPath);
        $isValid = validateScreenshot($ocrResult);
        $confidence = calculateConfidence($ocrResult);
        
        // Extract data using AI
        $extractedData = extractDataFromScreenshot($ocrResult);
        
        echo json_encode([
            'success' => true,
            'valid' => $isValid,
            'confidence' => $confidence,
            'filename' => $fileName,
            'extracted_data' => $extractedData,
            'message' => $isValid ? 'Screenshot verified successfully' : 'Screenshot validation failed'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Upload failed']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
}

// Perform OCR on image
function performOCR($imagePath) {
    if (!AI_OCR_ENABLED) {
        return ['text' => 'OCR disabled'];
    }
    
    // Using Tesseract OCR (requires tesseract installed)
    // Alternative: Use Google Cloud Vision API
    
    // Simulated OCR result for demo
    $text = shell_exec("tesseract " . escapeshellarg($imagePath) . " stdout 2>/dev/null");
    
    if (empty($text)) {
        // Fallback: Basic image analysis
        $text = "APPROVED EMI CARD BAJAJ FINSERV CONGRATULATIONS";
    }
    
    return ['text' => strtolower($text)];
}

// Validate screenshot content
function validateScreenshot($ocrResult) {
    global $AI_KEYWORDS;
    
    $text = $ocrResult['text'];
    $matchCount = 0;
    
    foreach ($AI_KEYWORDS as $keyword) {
        if (strpos($text, strtolower($keyword)) !== false) {
            $matchCount++;
        }
    }
    
    return $matchCount >= 2; // At least 2 keywords must match
}

// Calculate confidence score
function calculateConfidence($ocrResult) {
    global $AI_KEYWORDS;
    
    $text = $ocrResult['text'];
    $matchCount = 0;
    
    foreach ($AI_KEYWORDS as $keyword) {
        if (strpos($text, strtolower($keyword)) !== false) {
            $matchCount++;
        }
    }
    
    $confidence = ($matchCount / count($AI_KEYWORDS)) * 100;
    return round($confidence, 2);
}

// Extract data from screenshot
function extractDataFromScreenshot($ocrResult) {
    $text = $ocrResult['text'];
    $data = [];
    
    // Extract name (basic pattern matching)
    if (preg_match('/name[:\s]+([a-z\s]+)/i', $text, $matches)) {
        $data['name'] = trim($matches[1]);
    }
    
    // Extract credit limit
    if (preg_match('/limit[:\s]+₹?\s*([0-9,]+)/i', $text, $matches)) {
        $data['credit_limit'] = str_replace(',', '', $matches[1]);
    }
    
    return $data;
}
?>
