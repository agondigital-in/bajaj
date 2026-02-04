<?php
session_start();
require_once '../config/database.php';

// Simple authentication
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Fetch statistics
$totalApps = $conn->query("SELECT COUNT(*) as count FROM applications")->fetch_assoc()['count'];
$pendingApps = $conn->query("SELECT COUNT(*) as count FROM applications WHERE status='pending'")->fetch_assoc()['count'];
$approvedApps = $conn->query("SELECT COUNT(*) as count FROM applications WHERE status='approved'")->fetch_assoc()['count'];
$rejectedApps = $conn->query("SELECT COUNT(*) as count FROM applications WHERE status='rejected'")->fetch_assoc()['count'];

// Fetch recent applications
$applications = $conn->query("SELECT * FROM applications ORDER BY created_at DESC LIMIT 50");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <nav>
                <a href="index.php" class="active">Dashboard</a>
                <a href="applications.php">Applications</a>
                <a href="analytics.php">Analytics</a>
                <a href="settings.php">Settings</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="dashboard-header">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <span>Admin</span>
                </div>
            </header>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Applications</h3>
                    <p class="stat-number"><?php echo $totalApps; ?></p>
                </div>
                <div class="stat-card pending">
                    <h3>Pending</h3>
                    <p class="stat-number"><?php echo $pendingApps; ?></p>
                </div>
                <div class="stat-card approved">
                    <h3>Approved</h3>
                    <p class="stat-number"><?php echo $approvedApps; ?></p>
                </div>
                <div class="stat-card rejected">
                    <h3>Rejected</h3>
                    <p class="stat-number"><?php echo $rejectedApps; ?></p>
                </div>
            </div>

            <!-- Applications Table -->
            <div class="table-container">
                <h2>Recent Applications</h2>
                <table class="applications-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>UPI ID</th>
                            <th>Click ID</th>
                            <th>Screenshot</th>
                            <th>Risk Score</th>
                            <th>AI Verified</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($app = $applications->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $app['id']; ?></td>
                            <td><?php echo htmlspecialchars($app['name']); ?></td>
                            <td><?php echo htmlspecialchars($app['upi_id']); ?></td>
                            <td><?php echo htmlspecialchars($app['click_id']); ?></td>
                            <td>
                                <a href="#" onclick="viewScreenshot('<?php echo $app['screenshot_path']; ?>')">View</a>
                            </td>
                            <td>
                                <span class="risk-badge risk-<?php echo $app['risk_score'] < 30 ? 'low' : ($app['risk_score'] < 60 ? 'medium' : 'high'); ?>">
                                    <?php echo $app['risk_score']; ?>
                                </span>
                            </td>
                            <td>
                                <?php echo $app['ai_verified'] ? '✓' : '✗'; ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $app['status']; ?>">
                                    <?php echo ucfirst($app['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('Y-m-d H:i', strtotime($app['created_at'])); ?></td>
                            <td>
                                <button onclick="viewDetails(<?php echo $app['id']; ?>)">View</button>
                                <button onclick="updateStatus(<?php echo $app['id']; ?>, 'approved')">Approve</button>
                                <button onclick="updateStatus(<?php echo $app['id']; ?>, 'rejected')">Reject</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Screenshot Modal -->
    <div id="screenshotModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeScreenshotModal()">&times;</span>
            <img id="screenshotPreview" src="" alt="Screenshot">
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>
</html>
