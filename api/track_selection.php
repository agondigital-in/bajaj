<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);
$cardType = $input['card_type'];

// Update or insert analytics
$stmt = $conn->prepare("INSERT INTO image_analytics (card_type, views) VALUES (?, 1) ON DUPLICATE KEY UPDATE views = views + 1");
$stmt->bind_param("s", $cardType);
$stmt->execute();

echo json_encode(['success' => true]);
?>
