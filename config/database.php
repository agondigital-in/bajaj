<?php
// Simple .env loader
function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        return [];
    }
    
    $env = [];
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip comments and empty lines
        if (empty($line) || $line[0] === '#') {
            continue;
        }
        
        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes from value
            $value = trim($value, '"\'');
            
            $env[$key] = $value;
        }
    }
    
    return $env;
}

// Load environment variables
$envPath = __DIR__ . '/../';
$env = [];

// Try .env.local first (for local development)
if (file_exists($envPath . '.env.local')) {
    $env = loadEnv($envPath . '.env.local');
}
// Otherwise load .env
elseif (file_exists($envPath . '.env')) {
    $env = loadEnv($envPath . '.env');
}

// Get database configuration with fallbacks
$db_host = isset($env['DB_HOST']) ? $env['DB_HOST'] : 'localhost';
$db_user = isset($env['DB_USER']) ? $env['DB_USER'] : 'root';
$db_pass = isset($env['DB_PASS']) ? $env['DB_PASS'] : '';
$db_name = isset($env['DB_NAME']) ? $env['DB_NAME'] : 'emi_card_system';
$db_port = isset($env['DB_PORT']) ? $env['DB_PORT'] : '3306';

// Define constants
define('DB_HOST', $db_host);
define('DB_USER', $db_user);
define('DB_PASS', $db_pass);
define('DB_NAME', $db_name);
define('DB_PORT', (int)$db_port);

// Debug: Uncomment to see what values are being used
// echo "Host: " . DB_HOST . "<br>";
// echo "User: " . DB_USER . "<br>";
// echo "Port: " . DB_PORT . "<br>";
// echo "Database: " . DB_NAME . "<br>";
// die();

// Create connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    error_log("Database Error: " . $e->getMessage());
    error_log("Connection details - Host: " . DB_HOST . ", User: " . DB_USER . ", Port: " . DB_PORT);
    die("Database connection failed. Please check configuration.");
}
?>
