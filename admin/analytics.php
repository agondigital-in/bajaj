<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Get statistics
$totalApps = $conn->query("SELECT COUNT(*) as count FROM applications")->fetch_assoc()['count'];
$pendingApps = $conn->query("SELECT COUNT(*) as count FROM applications WHERE status='pending'")->fetch_assoc()['count'];
$approvedApps = $conn->query("SELECT COUNT(*) as count FROM applications WHERE status='approved'")->fetch_assoc()['count'];
$rejectedApps = $conn->query("SELECT COUNT(*) as count FROM applications WHERE status='rejected'")->fetch_assoc()['count'];

// Conversion rate
$conversionRate = $totalApps > 0 ? round(($approvedApps / $totalApps) * 100, 2) : 0;

// Average risk score
$avgRiskResult = $conn->query("SELECT AVG(risk_score) as avg_risk FROM applications");
$avgRisk = round($avgRiskResult->fetch_assoc()['avg_risk'], 2);

// Applications by date (last 7 days)
$dateQuery = "SELECT DATE(created_at) as date, COUNT(*) as count FROM applications WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY date ASC";
$dateResult = $conn->query($dateQuery);
$dateData = [];
while ($row = $dateResult->fetch_assoc()) {
    $dateData[] = $row;
}

// Top performing images
$imageQuery = "SELECT card_type, views, clicks, conversions FROM image_analytics ORDER BY conversions DESC LIMIT 5";
$imageResult = $conn->query($imageQuery);

// Risk distribution
$riskLow = $conn->query("SELECT COUNT(*) as count FROM applications WHERE risk_score < 30")->fetch_assoc()['count'];
$riskMedium = $conn->query("SELECT COUNT(*) as count FROM applications WHERE risk_score BETWEEN 30 AND 60")->fetch_assoc()['count'];
$riskHigh = $conn->query("SELECT COUNT(*) as count FROM applications WHERE risk_score > 60")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <nav>
                <a href="index.php">Dashboard</a>
                <a href="applications.php">Applications</a>
                <a href="analytics.php" class="active">Analytics</a>
                <a href="settings.php">Settings</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="dashboard-header">
                <h1>Analytics & Reports</h1>
                <div class="user-info">
                    <span>Admin</span>
                </div>
            </header>

            <!-- Key Metrics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Applications</h3>
                    <p class="stat-number"><?php echo $totalApps; ?></p>
                </div>
                <div class="stat-card approved">
                    <h3>Conversion Rate</h3>
                    <p class="stat-number"><?php echo $conversionRate; ?>%</p>
                </div>
                <div class="stat-card">
                    <h3>Average Risk Score</h3>
                    <p class="stat-number"><?php echo $avgRisk; ?></p>
                </div>
                <div class="stat-card pending">
                    <h3>Pending Review</h3>
                    <p class="stat-number"><?php echo $pendingApps; ?></p>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-section">
                <!-- Applications Trend -->
                <div class="chart-container">
                    <h2>Applications Trend (Last 7 Days)</h2>
                    <canvas id="trendChart"></canvas>
                </div>

                <!-- Status Distribution -->
                <div class="chart-container">
                    <h2>Status Distribution</h2>
                    <canvas id="statusChart"></canvas>
                </div>

                <!-- Risk Distribution -->
                <div class="chart-container">
                    <h2>Risk Score Distribution</h2>
                    <canvas id="riskChart"></canvas>
                </div>
            </div>

            <!-- Image Performance -->
            <div class="table-container">
                <h2>Image Performance</h2>
                <table class="applications-table">
                    <thead>
                        <tr>
                            <th>Card Type</th>
                            <th>Views</th>
                            <th>Clicks</th>
                            <th>Conversions</th>
                            <th>Conversion Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($imageResult->num_rows > 0): ?>
                            <?php while ($img = $imageResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($img['card_type']); ?></td>
                                <td><?php echo $img['views']; ?></td>
                                <td><?php echo $img['clicks']; ?></td>
                                <td><?php echo $img['conversions']; ?></td>
                                <td>
                                    <?php 
                                    $rate = $img['clicks'] > 0 ? round(($img['conversions'] / $img['clicks']) * 100, 2) : 0;
                                    echo $rate . '%';
                                    ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No data available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        // Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        const trendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($dateData, 'date')); ?>,
                datasets: [{
                    label: 'Applications',
                    data: <?php echo json_encode(array_column($dateData, 'count')); ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Approved', 'Rejected'],
                datasets: [{
                    data: [<?php echo $pendingApps; ?>, <?php echo $approvedApps; ?>, <?php echo $rejectedApps; ?>],
                    backgroundColor: ['#ffa500', '#28a745', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Risk Chart
        const riskCtx = document.getElementById('riskChart').getContext('2d');
        const riskChart = new Chart(riskCtx, {
            type: 'bar',
            data: {
                labels: ['Low Risk', 'Medium Risk', 'High Risk'],
                datasets: [{
                    label: 'Applications',
                    data: [<?php echo $riskLow; ?>, <?php echo $riskMedium; ?>, <?php echo $riskHigh; ?>],
                    backgroundColor: ['#28a745', '#ffa500', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
</body>
</html>
