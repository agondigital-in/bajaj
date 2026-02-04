<?php
// AI Configuration
define('AI_OCR_ENABLED', true);
define('AI_FRAUD_DETECTION', true);
define('AI_IMAGE_OPTIMIZATION', true);

// OCR API Configuration (Using Tesseract or Cloud Vision API)
define('OCR_API_KEY', 'your_api_key_here');
define('OCR_ENDPOINT', 'https://vision.googleapis.com/v1/images:annotate');

// AI Validation Keywords
$AI_KEYWORDS = [
    'approved',
    'emi card',
    'congratulations',
    'bajaj finserv',
    'credit limit'
];

// Risk Score Thresholds
define('RISK_LOW', 30);
define('RISK_MEDIUM', 60);
define('RISK_HIGH', 90);
?>
