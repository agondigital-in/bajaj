<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Filters
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$whereClause = "WHERE 1=1";
if ($statusFilter !== 'all') {
    $whereClause .= " AND status = '" . $conn->real_escape_string($statusFilter) . "'";
}
if (!empty($searchQuery)) {
    $whereClause .= " AND (name LIKE '%" . $conn->real_escape_string($searchQuery) . "%' OR upi_id LIKE '%" . $conn->real_escape_string($searchQuery) . "%')";
}

// Get total count
$totalQuery = "SELECT COUNT(*) as total FROM applications $whereClause";
$totalResult = $conn->query($totalQuery);
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $perPage);

// Get applications
$query = "SELECT * FROM applications $whereClause ORDER BY created_at DESC LIMIT $perPage OFFSET $offset";
$applications = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .applications-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }
        
        .applications-header h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
        }
        
        .applications-header p {
            margin: 0;
            opacity: 0.9;
        }
        
        .stats-mini {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        
        .stat-mini {
            background: rgba(255,255,255,0.2);
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }
        
        .stat-mini-number {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .stat-mini-label {
            font-size: 0.85rem;
            opacity: 0.9;
        }
        
        .filters-modern {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }
        
        .filter-row {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-item {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-item label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #666;
            margin-bottom: 0.5rem;
        }
        
        .filter-item select,
        .filter-item input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        
        .filter-item select:focus,
        .filter-item input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-search {
            padding: 0.75rem 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: transform 0.2s;
        }
        
        .btn-search:hover {
            transform: translateY(-2px);
        }
        
        .btn-export {
            padding: 0.75rem 1.5rem;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .applications-grid {
            display: grid;
            gap: 1rem;
        }
        
        .app-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
            border-left: 4px solid #667eea;
        }
        
        .app-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 25px rgba(0,0,0,0.12);
        }
        
        .app-card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }
        
        .app-id {
            font-size: 0.85rem;
            color: #999;
            font-weight: 600;
        }
        
        .app-name {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            margin: 0.25rem 0;
        }
        
        .app-card-body {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .app-info-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .app-info-label {
            font-size: 0.75rem;
            color: #999;
            text-transform: uppercase;
            font-weight: 600;
        }
        
        .app-info-value {
            font-size: 0.95rem;
            color: #333;
            font-weight: 500;
        }
        
        .app-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }
        
        .app-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            font-size: 0.9rem;
        }
        
        .btn-icon:hover {
            transform: scale(1.1);
        }
        
        .btn-view {
            background: #667eea;
            color: white;
        }
        
        .btn-approve {
            background: #28a745;
            color: white;
        }
        
        .btn-reject {
            background: #dc3545;
            color: white;
        }
        
        .status-badge-modern {
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .risk-badge-modern {
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .pagination-modern {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        
        .pagination-modern a,
        .pagination-modern .current-page {
            padding: 0.6rem 1rem;
            border-radius: 10px;
            text-decoration: none;
            color: #333;
            background: white;
            border: 2px solid #e0e0e0;
            transition: all 0.2s;
        }
        
        .pagination-modern a:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .pagination-modern .current-page {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 15px;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 1rem;
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
            <!-- Modern Header -->
            <div class="applications-header">
                <h1><i class="fas fa-file-alt"></i> Applications Management</h1>
                <p>Manage and review all EMI card applications</p>
                <div class="stats-mini">
                    <div class="stat-mini">
                        <div class="stat-mini-number"><?php echo $totalRows; ?></div>
                        <div class="stat-mini-label">Total</div>
                    </div>
                    <div class="stat-mini">
                        <div class="stat-mini-number">
                            <?php echo $conn->query("SELECT COUNT(*) as count FROM applications WHERE status='pending'")->fetch_assoc()['count']; ?>
                        </div>
                        <div class="stat-mini-label">Pending</div>
                    </div>
                    <div class="stat-mini">
                        <div class="stat-mini-number">
                            <?php echo $conn->query("SELECT COUNT(*) as count FROM applications WHERE status='approved'")->fetch_assoc()['count']; ?>
                        </div>
                        <div class="stat-mini-label">Approved</div>
                    </div>
                </div>
            </div>

            <!-- Modern Filters -->
            <div class="filters-modern">
                <form method="GET" class="filter-row">
                    <div class="filter-item">
                        <label><i class="fas fa-filter"></i> Status</label>
                        <select name="status">
                            <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Status</option>
                            <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>⏳ Pending</option>
                            <option value="approved" <?php echo $statusFilter === 'approved' ? 'selected' : ''; ?>>✓ Approved</option>
                            <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>✗ Rejected</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label><i class="fas fa-search"></i> Search</label>
                        <input type="text" name="search" placeholder="Search by name or UPI..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                    </div>
                    <div style="display: flex; gap: 0.5rem; align-items: flex-end;">
                        <button type="submit" class="btn-search">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <button type="button" onclick="exportData()" class="btn-export">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </form>
            </div>

            <!-- Applications Grid -->
            <div class="applications-grid">
                <?php if ($applications->num_rows > 0): ?>
                    <?php while ($app = $applications->fetch_assoc()): ?>
                    <div class="app-card">
                        <div class="app-card-header">
                            <div>
                                <div class="app-id">#<?php echo $app['id']; ?></div>
                                <div class="app-name"><?php echo htmlspecialchars($app['name']); ?></div>
                            </div>
                            <span class="status-badge-modern status-<?php echo $app['status']; ?>">
                                <?php 
                                if ($app['status'] === 'pending') echo '⏳';
                                elseif ($app['status'] === 'approved') echo '✓';
                                else echo '✗';
                                ?>
                                <?php echo ucfirst($app['status']); ?>
                            </span>
                        </div>
                        
                        <div class="app-card-body">
                            <div class="app-info-item">
                                <span class="app-info-label"><i class="fas fa-wallet"></i> UPI ID</span>
                                <span class="app-info-value"><?php echo htmlspecialchars($app['upi_id']); ?></span>
                            </div>
                            <div class="app-info-item">
                                <span class="app-info-label"><i class="fas fa-link"></i> Click ID</span>
                                <span class="app-info-value"><?php echo htmlspecialchars($app['click_id']); ?></span>
                            </div>
                            <div class="app-info-item">
                                <span class="app-info-label"><i class="fas fa-shield-alt"></i> Risk Score</span>
                                <span class="app-info-value">
                                    <span class="risk-badge-modern risk-<?php echo $app['risk_score'] < 30 ? 'low' : ($app['risk_score'] < 60 ? 'medium' : 'high'); ?>">
                                        <?php echo $app['risk_score']; ?>/100
                                    </span>
                                </span>
                            </div>
                            <div class="app-info-item">
                                <span class="app-info-label"><i class="fas fa-robot"></i> AI Verified</span>
                                <span class="app-info-value">
                                    <?php echo $app['ai_verified'] ? '<span style="color: #28a745;">✓ Yes</span>' : '<span style="color: #dc3545;">✗ No</span>'; ?>
                                </span>
                            </div>
                            <div class="app-info-item">
                                <span class="app-info-label"><i class="fas fa-calendar"></i> Submitted</span>
                                <span class="app-info-value"><?php echo date('d M Y', strtotime($app['created_at'])); ?></span>
                            </div>
                            <div class="app-info-item">
                                <span class="app-info-label"><i class="fas fa-image"></i> Screenshot</span>
                                <span class="app-info-value">
                                    <?php if (!empty($app['screenshot_path'])): ?>
                                        <a href="#" onclick="viewScreenshot('<?php echo $app['screenshot_path']; ?>')" style="color: #667eea;">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #999;">None</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="app-card-footer">
                            <div style="font-size: 0.85rem; color: #999;">
                                <i class="fas fa-clock"></i> <?php echo date('h:i A', strtotime($app['created_at'])); ?>
                            </div>
                            <div class="app-actions">
                                <button onclick="viewDetails(<?php echo $app['id']; ?>)" class="btn-icon btn-view" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if ($app['status'] === 'pending'): ?>
                                    <button onclick="updateStatus(<?php echo $app['id']; ?>, 'approved')" class="btn-icon btn-approve" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button onclick="updateStatus(<?php echo $app['id']; ?>, 'rejected')" class="btn-icon btn-reject" title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h2>No Applications Found</h2>
                        <p>There are no applications matching your criteria.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Modern Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination-modern">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo urlencode($searchQuery); ?>">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= min($totalPages, 5); $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current-page"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo urlencode($searchQuery); ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($totalPages > 5): ?>
                    <span>...</span>
                    <a href="?page=<?php echo $totalPages; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo urlencode($searchQuery); ?>"><?php echo $totalPages; ?></a>
                <?php endif; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo urlencode($searchQuery); ?>">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
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
    <script>
        function exportData() {
            window.location.href = 'export_csv.php?status=<?php echo $statusFilter; ?>&search=<?php echo urlencode($searchQuery); ?>';
        }
    </script>
</body>
</html>
