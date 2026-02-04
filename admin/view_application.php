<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Get application ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id === 0) {
    header('Location: applications.php');
    exit;
}

// Fetch application details
$stmt = $conn->prepare("SELECT * FROM applications WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: applications.php');
    exit;
}

$app = $result->fetch_assoc();

// Fetch click tracking info
$clickStmt = $conn->prepare("SELECT * FROM click_tracking WHERE click_id = ?");
$clickStmt->bind_param("s", $app['click_id']);
$clickStmt->execute();
$clickResult = $clickStmt->get_result();
$clickData = $clickResult->fetch_assoc();

// Fetch activity logs
$logsStmt = $conn->prepare("SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$logsStmt->bind_param("i", $id);
$logsStmt->execute();
$logsResult = $logsStmt->get_result();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = $_POST['status'];
    $notes = $_POST['notes'];
    
    $updateStmt = $conn->prepare("UPDATE applications SET status = ?, updated_at = NOW() WHERE id = ?");
    $updateStmt->bind_param("si", $newStatus, $id);
    
    if ($updateStmt->execute()) {
        // Log the action
        $logStmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, 'status_updated', ?, ?)");
        $logDetails = "Status changed to: $newStatus. Notes: $notes";
        $ip = $_SERVER['REMOTE_ADDR'];
        $logStmt->bind_param("iss", $id, $logDetails, $ip);
        $logStmt->execute();
        
        header("Location: view_application.php?id=$id&success=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Application #<?php echo $id; ?> - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .detail-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .detail-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .detail-card h2 {
            margin-bottom: 1rem;
            color: #667eea;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .detail-label {
            font-weight: 600;
            color: #666;
        }
        
        .detail-value {
            color: #333;
            text-align: right;
        }
        
        .screenshot-preview {
            width: 100%;
            max-width: 500px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            cursor: pointer;
        }
        
        .screenshot-placeholder {
            width: 100%;
            height: 300px;
            background: #f8f9fa;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
        }
        
        .status-update-form {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
        }
        
        .status-update-form select,
        .status-update-form textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .back-button {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .back-button:hover {
            background: #5a6268;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .timeline {
            position: relative;
            padding-left: 2rem;
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 1rem;
            border-left: 2px solid #ddd;
            padding-left: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -6px;
            top: 0;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #667eea;
        }
        
        .timeline-date {
            font-size: 0.85rem;
            color: #999;
        }
        
        @media (max-width: 768px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <nav>
                <a href="index.php">Dashboard</a>
                <a href="applications.php" class="active">Applications</a>
                <a href="analytics.php">Analytics</a>
                <a href="settings.php">Settings</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="detail-container">
                <a href="applications.php" class="back-button">← Back to Applications</a>
                
                <?php if (isset($_GET['success'])): ?>
                    <div class="success-message">Status updated successfully!</div>
                <?php endif; ?>
                
                <h1>Application Details #<?php echo $id; ?></h1>
                
                <div class="detail-grid">
                    <!-- Main Details -->
                    <div>
                        <div class="detail-card">
                            <h2>Personal Information</h2>
                            <div class="detail-row">
                                <span class="detail-label">Application ID:</span>
                                <span class="detail-value"><?php echo $app['id']; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Name:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($app['name']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">UPI ID:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($app['upi_id']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Click ID:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($app['click_id']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Status:</span>
                                <span class="detail-value">
                                    <span class="status-badge status-<?php echo $app['status']; ?>">
                                        <?php echo ucfirst($app['status']); ?>
                                    </span>
                                </span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Submitted:</span>
                                <span class="detail-value"><?php echo date('d M Y, h:i A', strtotime($app['created_at'])); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Last Updated:</span>
                                <span class="detail-value"><?php echo date('d M Y, h:i A', strtotime($app['updated_at'])); ?></span>
                            </div>
                        </div>

                        <!-- AI Verification -->
                        <div class="detail-card">
                            <h2>AI Verification</h2>
                            <div class="detail-row">
                                <span class="detail-label">AI Verified:</span>
                                <span class="detail-value">
                                    <?php echo $app['ai_verified'] ? '✓ Yes' : '✗ No'; ?>
                                </span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Risk Score:</span>
                                <span class="detail-value">
                                    <span class="risk-badge risk-<?php echo $app['risk_score'] < 30 ? 'low' : ($app['risk_score'] < 60 ? 'medium' : 'high'); ?>">
                                        <?php echo $app['risk_score']; ?>/100
                                    </span>
                                </span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Risk Level:</span>
                                <span class="detail-value">
                                    <?php 
                                    if ($app['risk_score'] < 30) {
                                        echo '<span style="color: #28a745;">Low Risk</span>';
                                    } elseif ($app['risk_score'] < 60) {
                                        echo '<span style="color: #ffa500;">Medium Risk</span>';
                                    } else {
                                        echo '<span style="color: #dc3545;">High Risk</span>';
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>

                        <!-- Tracking Information -->
                        <?php if ($clickData): ?>
                        <div class="detail-card">
                            <h2>Tracking Information</h2>
                            <div class="detail-row">
                                <span class="detail-label">IP Address:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($clickData['ip_address']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">User Agent:</span>
                                <span class="detail-value" style="font-size: 0.85rem;">
                                    <?php echo htmlspecialchars(substr($clickData['user_agent'], 0, 50)) . '...'; ?>
                                </span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Tracking URL:</span>
                                <span class="detail-value">
                                    <a href="<?php echo htmlspecialchars($clickData['tracking_url']); ?>" target="_blank" style="color: #667eea;">View</a>
                                </span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Click Time:</span>
                                <span class="detail-value"><?php echo date('d M Y, h:i A', strtotime($clickData['created_at'])); ?></span>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Activity Timeline -->
                        <div class="detail-card">
                            <h2>Activity Timeline</h2>
                            <div class="timeline">
                                <?php if ($logsResult->num_rows > 0): ?>
                                    <?php while ($log = $logsResult->fetch_assoc()): ?>
                                        <div class="timeline-item">
                                            <strong><?php echo htmlspecialchars($log['action']); ?></strong>
                                            <p style="margin: 0.25rem 0; color: #666;">
                                                <?php echo htmlspecialchars($log['details']); ?>
                                            </p>
                                            <span class="timeline-date">
                                                <?php echo date('d M Y, h:i A', strtotime($log['created_at'])); ?>
                                            </span>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p style="color: #999;">No activity logs available</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div>
                        <!-- Screenshot -->
                        <div class="detail-card">
                            <h2>Screenshot</h2>
                            <?php if (!empty($app['screenshot_path'])): ?>
                                <img src="../uploads/screenshots/<?php echo htmlspecialchars($app['screenshot_path']); ?>" 
                                     alt="Screenshot" 
                                     class="screenshot-preview"
                                     onclick="openFullscreen(this.src)">
                            <?php else: ?>
                                <div class="screenshot-placeholder">
                                    <p>No screenshot uploaded</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Update Status -->
                        <div class="detail-card">
                            <h2>Update Status</h2>
                            <form method="POST" class="status-update-form">
                                <label>New Status:</label>
                                <select name="status" required>
                                    <option value="pending" <?php echo $app['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="approved" <?php echo $app['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="rejected" <?php echo $app['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                                
                                <label>Notes (Optional):</label>
                                <textarea name="notes" rows="3" placeholder="Add any notes..."></textarea>
                                
                                <button type="submit" name="update_status" class="btn-primary">Update Status</button>
                            </form>
                        </div>

                        <!-- Quick Actions -->
                        <div class="detail-card">
                            <h2>Quick Actions</h2>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <button onclick="printApplication()" class="btn-primary">Print Details</button>
                                <button onclick="exportPDF()" class="btn-primary">Export as PDF</button>
                                <button onclick="deleteApplication(<?php echo $id; ?>)" class="btn-danger">Delete Application</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Fullscreen Modal -->
    <div id="fullscreenModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 90%; max-height: 90vh;">
            <span class="close" onclick="closeFullscreen()">&times;</span>
            <img id="fullscreenImage" src="" style="width: 100%; height: auto;">
        </div>
    </div>

    <script>
        function openFullscreen(src) {
            document.getElementById('fullscreenImage').src = src;
            document.getElementById('fullscreenModal').style.display = 'block';
        }

        function closeFullscreen() {
            document.getElementById('fullscreenModal').style.display = 'none';
        }

        function printApplication() {
            window.print();
        }

        function exportPDF() {
            alert('PDF export feature - Integrate with jsPDF or server-side PDF generator');
        }

        function deleteApplication(id) {
            if (confirm('Are you sure you want to delete this application? This action cannot be undone!')) {
                window.location.href = 'delete_application.php?id=' + id;
            }
        }

        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('fullscreenModal');
            if (event.target == modal) {
                closeFullscreen();
            }
        }
    </script>
</body>
</html>
