<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

$lastCheck = isset($_SESSION['last_check']) ? $_SESSION['last_check'] : date('Y-m-d H:i:s', strtotime('-1 hour'));

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM applications WHERE created_at > ?");
$stmt->bind_param("s", $lastCheck);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$_SESSION['last_check'] = date('Y-m-d H:i:s');

echo json_encode(['success' => true, 'new_count' => $row['count']]);
?>
