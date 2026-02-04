<?php
// Test database connection
require_once 'config/database.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Database connected successfully!<br>";

// Check if tables exist
$tables = ['applications', 'click_tracking', 'activity_logs', 'image_analytics', 'admin_users'];

foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "✓ Table '$table' exists<br>";
    } else {
        echo "✗ Table '$table' NOT found<br>";
    }
}

echo "<br><a href='index.php'>Go to Home</a>";
?>
