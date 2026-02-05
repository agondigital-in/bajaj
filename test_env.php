<?php
// Test page to check .env loading
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Environment Variables Test</h1>";
echo "<hr>";

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

// Check if .env files exist
echo "<h2>1. File Check</h2>";
$envPath = __DIR__ . '/';
$envFile = $envPath . '.env';
$envLocalFile = $envPath . '.env.local';

echo ".env file exists: " . (file_exists($envFile) ? "✅ YES" : "❌ NO") . "<br>";
echo ".env.local file exists: " . (file_exists($envLocalFile) ? "✅ YES" : "❌ NO") . "<br>";
echo "<hr>";

// Load environment variables
echo "<h2>2. Loading .env Values</h2>";
$env = [];

if (file_exists($envLocalFile)) {
    $env = loadEnv($envLocalFile);
    echo "Loaded from: <strong>.env.local</strong><br>";
} elseif (file_exists($envFile)) {
    $env = loadEnv($envFile);
    echo "Loaded from: <strong>.env</strong><br>";
} else {
    echo "❌ No .env file found!<br>";
}

echo "Total variables loaded: <strong>" . count($env) . "</strong><br>";
echo "<hr>";

// Display database configuration
echo "<h2>3. Database Configuration</h2>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Variable</th><th>Value</th></tr>";

$dbVars = ['DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME', 'DB_PORT'];
foreach ($dbVars as $var) {
    $value = isset($env[$var]) ? $env[$var] : 'NOT SET';
    
    // Hide password partially
    if ($var === 'DB_PASS' && $value !== 'NOT SET') {
        $value = substr($value, 0, 10) . '...' . substr($value, -10);
    }
    
    echo "<tr>";
    echo "<td><strong>$var</strong></td>";
    echo "<td>" . htmlspecialchars($value) . "</td>";
    echo "</tr>";
}
echo "</table>";
echo "<hr>";

// Test database connection
echo "<h2>4. Database Connection Test</h2>";

$db_host = isset($env['DB_HOST']) ? $env['DB_HOST'] : 'localhost';
$db_user = isset($env['DB_USER']) ? $env['DB_USER'] : 'root';
$db_pass = isset($env['DB_PASS']) ? $env['DB_PASS'] : '';
$db_name = isset($env['DB_NAME']) ? $env['DB_NAME'] : 'emi_card_system';
$db_port = isset($env['DB_PORT']) ? $env['DB_PORT'] : '3306';

echo "Attempting connection with:<br>";
echo "Host: <strong>$db_host</strong><br>";
echo "User: <strong>$db_user</strong><br>";
echo "Port: <strong>$db_port</strong><br>";
echo "Database: <strong>$db_name</strong><br><br>";

try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
    
    if ($conn->connect_error) {
        echo "❌ <span style='color: red;'>Connection FAILED</span><br>";
        echo "Error: " . $conn->connect_error . "<br>";
    } else {
        echo "✅ <span style='color: green;'>Connection SUCCESSFUL!</span><br>";
        echo "Server version: " . $conn->server_info . "<br>";
        $conn->close();
    }
} catch (Exception $e) {
    echo "❌ <span style='color: red;'>Exception: " . $e->getMessage() . "</span><br>";
}

echo "<hr>";

// Display all loaded environment variables
echo "<h2>5. All Environment Variables</h2>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Key</th><th>Value</th></tr>";

foreach ($env as $key => $value) {
    // Hide sensitive values
    if (strpos($key, 'PASS') !== false || strpos($key, 'SECRET') !== false) {
        $displayValue = substr($value, 0, 5) . '***' . substr($value, -5);
    } else {
        $displayValue = $value;
    }
    
    echo "<tr>";
    echo "<td><strong>" . htmlspecialchars($key) . "</strong></td>";
    echo "<td>" . htmlspecialchars($displayValue) . "</td>";
    echo "</tr>";
}

echo "</table>";
echo "<hr>";

echo "<p><strong>Test completed!</strong></p>";
echo "<p><a href='index.php'>Go to Main Page</a></p>";
?>
