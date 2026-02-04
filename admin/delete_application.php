<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id === 0) {
    header('Location: applications.php');
    exit;
}

// Delete application
$stmt = $conn->prepare("DELETE FROM applications WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Also delete related logs
    $logStmt = $conn->prepare("DELETE FROM activity_logs WHERE user_id = ?");
    $logStmt->bind_param("i", $id);
    $logStmt->execute();
    
    header('Location: applications.php?deleted=1');
} else {
    header('Location: applications.php?error=1');
}
exit;
?>
