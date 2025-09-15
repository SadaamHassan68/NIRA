<?php
/**
 * NIRA System - Admin Dashboard
 * Somalia National Identification & Registration Authority
 */

session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get dashboard statistics
$stats = [];

// Total citizens
$stmt = $pdo->query("SELECT COUNT(*) as total FROM citizens");
$stats['total_citizens'] = $stmt->fetch()['total'];

// Pending applications
$stmt = $pdo->query("SELECT COUNT(*) as total FROM citizens WHERE status = 'pending'");
$stats['pending_applications'] = $stmt->fetch()['total'];

// Approved applications
$stmt = $pdo->query("SELECT COUNT(*) as total FROM citizens WHERE status = 'approved'");
$stats['approved_citizens'] = $stmt->fetch()['total'];

// Today's verifications
$stmt = $pdo->query("SELECT COUNT(*) as total FROM verification_logs WHERE DATE(created_at) = CURDATE()");
$stats['today_verifications'] = $stmt->fetch()['total'];

// Recent applications
$stmt = $pdo->query("
    SELECT nin, full_name, region, status, created_at 
    FROM citizens 
    ORDER BY created_at DESC 
    LIMIT 10
");
$recent_applications = $stmt->fetchAll();

// Regional statistics
$stmt = $pdo->query("
    SELECT region, COUNT(*) as count 
    FROM citizens 
    WHERE status = 'approved' 
    GROUP BY region 
    ORDER BY count DESC
");
$regional_stats = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Somalia NIRA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-id-card me-2"></i>
                <strong>Somalia NIRA</strong>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="citizens.php">
                            <i class="fas fa-users me-1"></i>Citizens
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="applications.php">
                            <i class="fas fa-file-alt me-1"></i>Applications
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="id-cards.php">
                            <i class="fas fa-id-card me-1"></i>ID Cards
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="verification.php">
                            <i class="fas fa-search me-1"></i>Verification
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
                            <i class="fas fa-chart-bar me-1"></i>Reports
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['admin_name']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">
                                <i class="fas fa-user-edit me-2"></i>Profile
                            </a></li>
                            <li><a class="dropdown-item" href="settings.php">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2 class="mb-1">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</h2>
                    <p class="text-muted mb-0">Here's what's happening with the NIRA system today.</p>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['total_citizens']); ?></div>
                    <div class="stat-label">Total Citizens</div>
                    <i class="fas fa-users fa-2x opacity-75 position-absolute top-0 end-0 me-3 mt-2"></i>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card bg-warning">
                    <div class="stat-number"><?php echo number_format($stats['pending_applications']); ?></div>
                    <div class="stat-label">Pending Applications</div>
                    <i class="fas fa-clock fa-2x opacity-75 position-absolute top-0 end-0 me-3 mt-2"></i>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card bg-success">
                    <div class="stat-number"><?php echo number_format($stats['approved_citizens']); ?></div>
                    <div class="stat-label">Approved Citizens</div>
                    <i class="fas fa-check-circle fa-2x opacity-75 position-absolute top-0 end-0 me-3 mt-2"></i>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card bg-info">
                    <div class="stat-number"><?php echo number_format($stats['today_verifications']); ?></div>
                    <div class="stat-label">Today's Verifications</div>
                    <i class="fas fa-search fa-2x opacity-75 position-absolute top-0 end-0 me-3 mt-2"></i>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Applications -->
            <div class="col-lg-8 mb-4">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>Recent Applications
                        </h5>
                        <a href="applications.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>NIN</th>
                                    <th>Name</th>
                                    <th>Region</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_applications as $app): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($app['nin']); ?></code></td>
                                    <td><?php echo htmlspecialchars($app['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($app['region']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $app['status'] === 'approved' ? 'success' : ($app['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                            <?php echo ucfirst($app['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($app['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="view-citizen.php?id=<?php echo $app['nin']; ?>" 
                                               class="btn btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($app['status'] === 'pending'): ?>
                                            <button class="btn btn-outline-success" title="Approve" 
                                                    onclick="updateStatus('<?php echo $app['nin']; ?>', 'approved')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" title="Reject" 
                                                    onclick="updateStatus('<?php echo $app['nin']; ?>', 'rejected')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Regional Statistics -->
            <div class="col-lg-4 mb-4">
                <div class="dashboard-card">
                    <h5 class="mb-3">
                        <i class="fas fa-map-marker-alt me-2"></i>Regional Distribution
                    </h5>
                    
                    <?php foreach ($regional_stats as $region): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><?php echo htmlspecialchars($region['region']); ?></span>
                        <span class="badge bg-primary"><?php echo $region['count']; ?></span>
                    </div>
                    <div class="progress mb-3" style="height: 6px;">
                        <div class="progress-bar" style="width: <?php echo ($region['count'] / max(array_column($regional_stats, 'count'))) * 100; ?>%"></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="dashboard-card">
                    <h5 class="mb-3">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="applications.php?status=pending" class="btn btn-warning w-100">
                                <i class="fas fa-clock me-2"></i>Review Pending
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="id-cards.php" class="btn btn-success w-100">
                                <i class="fas fa-id-card me-2"></i>Generate ID Cards
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="verification.php" class="btn btn-info w-100">
                                <i class="fas fa-search me-2"></i>Verify Citizen
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="reports.php" class="btn btn-primary w-100">
                                <i class="fas fa-chart-bar me-2"></i>View Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        function updateStatus(nin, status) {
            if (confirm(`Are you sure you want to ${status} this application?`)) {
                fetch('api/update-status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        nin: nin,
                        status: status
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        location.reload();
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(error => {
                    showAlert('An error occurred. Please try again.', 'danger');
                    console.error('Error:', error);
                });
            }
        }
    </script>
</body>
</html>
