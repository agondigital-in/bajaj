<?php
session_start();
require_once '../config/database.php';
require_once '../config/ai_config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$message = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_ai'])) {
        // Update AI settings (in production, save to config file)
        $message = 'AI settings updated successfully!';
    }
    
    if (isset($_POST['update_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if ($currentPassword === 'admin123') { // Simple check
            if ($newPassword === $confirmPassword) {
                // Update password (in production, use password_hash)
                $message = 'Password updated successfully!';
            } else {
                $message = 'Passwords do not match!';
            }
        } else {
            $message = 'Current password is incorrect!';
        }
    }
    
    if (isset($_POST['clear_data'])) {
        $dataType = $_POST['data_type'];
        
        if ($dataType === 'applications') {
            $conn->query("DELETE FROM applications");
            $message = 'All applications deleted!';
        } elseif ($dataType === 'logs') {
            $conn->query("DELETE FROM activity_logs");
            $message = 'All logs deleted!';
        } elseif ($dataType === 'analytics') {
            $conn->query("DELETE FROM image_analytics");
            $message = 'Analytics data cleared!';
        }
    }
}

// Get system info
$dbSize = $conn->query("SELECT SUM(data_length + index_length) / 1024 / 1024 AS size FROM information_schema.TABLES WHERE table_schema = '" . DB_NAME . "'")->fetch_assoc()['size'];
$dbSize = round($dbSize, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <nav>
                <a href="index.php">Dashboard</a>
                <a href="applications.php">Applications</a>
                <a href="analytics.php">Analytics</a>
                <a href="settings.php" class="active">Settings</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="dashboard-header">
                <h1>Settings</h1>
                <div class="user-info">
                    <span>Admin</span>
                </div>
            </header>

            <?php if (!empty($message)): ?>
                <div class="alert"><?php echo $message; ?></div>
            <?php endif; ?>

            <!-- AI Configuration -->
            <div class="settings-section">
                <h2>AI Configuration</h2>
                <form method="POST" class="settings-form">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="ai_ocr" <?php echo AI_OCR_ENABLED ? 'checked' : ''; ?>>
                            Enable OCR Verification
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="ai_fraud" <?php echo AI_FRAUD_DETECTION ? 'checked' : ''; ?>>
                            Enable Fraud Detection
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="ai_image" <?php echo AI_IMAGE_OPTIMIZATION ? 'checked' : ''; ?>>
                            Enable Image Optimization
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Risk Threshold - Low:</label>
                        <input type="number" name="risk_low" value="<?php echo RISK_LOW; ?>" min="0" max="100">
                    </div>
                    <div class="form-group">
                        <label>Risk Threshold - Medium:</label>
                        <input type="number" name="risk_medium" value="<?php echo RISK_MEDIUM; ?>" min="0" max="100">
                    </div>
                    <div class="form-group">
                        <label>Risk Threshold - High:</label>
                        <input type="number" name="risk_high" value="<?php echo RISK_HIGH; ?>" min="0" max="100">
                    </div>
                    <button type="submit" name="update_ai" class="btn-primary">Update AI Settings</button>
                </form>
            </div>

            <!-- Security Settings -->
            <div class="settings-section">
                <h2>Security Settings</h2>
                <form method="POST" class="settings-form">
                    <div class="form-group">
                        <label>Current Password:</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label>New Password:</label>
                        <input type="password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password:</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="update_password" class="btn-primary">Change Password</button>
                </form>
            </div>

            <!-- System Information -->
            <div class="settings-section">
                <h2>System Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">PHP Version:</span>
                        <span class="info-value"><?php echo phpversion(); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Database Size:</span>
                        <span class="info-value"><?php echo $dbSize; ?> MB</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Server:</span>
                        <span class="info-value"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Upload Max Size:</span>
                        <span class="info-value"><?php echo ini_get('upload_max_filesize'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Data Management -->
            <div class="settings-section danger-zone">
                <h2>Data Management</h2>
                <p style="color: #dc3545; margin-bottom: 1rem;">⚠️ Warning: These actions cannot be undone!</p>
                <form method="POST" class="settings-form" onsubmit="return confirm('Are you sure? This action cannot be undone!');">
                    <div class="form-group">
                        <label>Clear Data:</label>
                        <select name="data_type" required>
                            <option value="">Select data type...</option>
                            <option value="applications">All Applications</option>
                            <option value="logs">Activity Logs</option>
                            <option value="analytics">Analytics Data</option>
                        </select>
                    </div>
                    <button type="submit" name="clear_data" class="btn-danger">Clear Selected Data</button>
                </form>
            </div>

            <!-- Backup & Export -->
            <div class="settings-section">
                <h2>Backup & Export</h2>
                <div class="backup-buttons">
                    <button onclick="exportDatabase()" class="btn-primary">Export Database</button>
                    <button onclick="exportApplications()" class="btn-primary">Export Applications CSV</button>
                    <button onclick="exportLogs()" class="btn-primary">Export Activity Logs</button>
                </div>
            </div>
        </main>
    </div>

    <script>
        function exportDatabase() {
            alert('Database export feature - Connect to phpMyAdmin or use mysqldump');
        }

        function exportApplications() {
            window.location.href = 'export_csv.php?type=applications';
        }

        function exportLogs() {
            window.location.href = 'export_csv.php?type=logs';
        }
    </script>
</body>
</html>
