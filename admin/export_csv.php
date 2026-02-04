<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$type = isset($_GET['type']) ? $_GET['type'] : 'applications';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $type . '_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

if ($type === 'applications') {
    // CSV headers
    fputcsv($output, ['ID', 'Name', 'UPI ID', 'Click ID', 'Screenshot', 'Risk Score', 'AI Verified', 'Status', 'Created At']);
    
    // Build query
    $whereClause = "WHERE 1=1";
    if ($status !== 'all') {
        $whereClause .= " AND status = '" . $conn->real_escape_string($status) . "'";
    }
    if (!empty($search)) {
        $whereClause .= " AND (name LIKE '%" . $conn->real_escape_string($search) . "%' OR upi_id LIKE '%" . $conn->real_escape_string($search) . "%')";
    }
    
    $query = "SELECT * FROM applications $whereClause ORDER BY created_at DESC";
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['name'],
            $row['upi_id'],
            $row['click_id'],
            $row['screenshot_path'],
            $row['risk_score'],
            $row['ai_verified'] ? 'Yes' : 'No',
            $row['status'],
            $row['created_at']
        ]);
    }
} elseif ($type === 'logs') {
    // CSV headers
    fputcsv($output, ['ID', 'User ID', 'Action', 'Details', 'IP Address', 'Created At']);
    
    $query = "SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 1000";
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['user_id'],
            $row['action'],
            $row['details'],
            $row['ip_address'],
            $row['created_at']
        ]);
    }
}

fclose($output);
exit;
?>
