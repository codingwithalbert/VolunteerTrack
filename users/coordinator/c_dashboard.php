<?php
require_once '../../includes/auth.php';
require_once '../../config/dbconfig.php';

requireRole('coordinator');

$database = new Database();
$db = $database->getConnection();

// Get statistics
$query = "SELECT COUNT(*) as total FROM users WHERE role = 'volunteer'";
$stmt = $db->prepare($query);
$stmt->execute();
$total_volunteers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM activities WHERE status = 'active'";
$stmt = $db->prepare($query);
$stmt->execute();
$total_activities = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM hours";
$stmt = $db->prepare($query);
$stmt->execute();
$total_hours_logged = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT SUM(hours_worked) as total FROM hours WHERE status = 'verified'";
$stmt = $db->prepare($query);
$stmt->execute();
$total_verified_hours = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$query = "SELECT COUNT(*) as total FROM hours WHERE status = 'pending'";
$stmt = $db->prepare($query);
$stmt->execute();
$pending_hours = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Recent hours submissions needing verification
$query = "SELECT h.*, u.full_name, a.activity_name 
          FROM hours h
          JOIN users u ON h.volunteer_id = u.user_id
          JOIN activities a ON h.activity_id = a.activity_id
          WHERE h.status = 'pending'
          ORDER BY h.created_at DESC
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$pending_submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coordinator Dashboard - <?php echo SITE_NAME; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 24px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-info span {
            font-size: 14px;
        }
        
        .header-links a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            padding: 8px 16px;
            border-radius: 5px;
            background: rgba(255,255,255,0.2);
            transition: background 0.3s;
        }
        
        .header-links a:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .welcome {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .welcome h2 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .welcome p {
            color: #666;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .stat-info h3 {
            font-size: 32px;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-info p {
            color: #666;
            font-size: 14px;
        }
        
        .stat-icon {
            font-size: 40px;
            opacity: 0.3;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .action-btn {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .action-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .action-btn h4 {
            margin-top: 10px;
            color: #667eea;
        }
        
        .recent-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .recent-section h3 {
            color: #333;
            margin-bottom: 20px;
        }
        
        .recent-item {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .recent-item:last-child {
            border-bottom: none;
        }
        
        .recent-info {
            flex: 1;
        }
        
        .recent-info h4 {
            color: #333;
            margin-bottom: 5px;
        }
        
        .recent-info p {
            color: #666;
            font-size: 13px;
        }
        
        .badge {
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .actions {
            display: flex;
            gap: 5px;
            margin-top: 10px;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1><?php echo SITE_NAME; ?></h1>
            <div class="user-info">
                <span>Welcome, <?php echo getUserFullName(); ?></span>
                <div class="header-links">
                    <a href="profile.php">Profile</a>
                    <a href="../../logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="welcome">
            <h2>Coordinator Dashboard</h2>
            <p>Manage activities and verify volunteer hours</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <h3><?php echo $total_volunteers; ?></h3>
                    <p>Total Volunteers</p>
                </div>
                <div class="stat-icon">👥</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3><?php echo $total_activities; ?></h3>
                    <p>Active Activities</p>
                </div>
                <div class="stat-icon">📋</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3><?php echo number_format($total_verified_hours, 1); ?></h3>
                    <p>Verified Hours</p>
                </div>
                <div class="stat-icon">⏱️</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3><?php echo $pending_hours; ?></h3>
                    <p>Pending Approvals</p>
                </div>
                <div class="stat-icon">⏳</div>
            </div>
        </div>
        
        <h3 style="color: #333; margin-bottom: 15px;">Quick Actions</h3>
        <div class="quick-actions">
            <a href="modules/activities/manage_activities.php" class="action-btn">
                <div style="font-size: 32px;">📝</div>
                <h4>Manage Activities</h4>
            </a>
            <a href="modules/hours/manage_hours.php" class="action-btn">
                <div style="font-size: 32px;">⏰</div>
                <h4>Verify Hours</h4>
            </a>
            <a href="modules/activities/add_activity.php" class="action-btn">
                <div style="font-size: 32px;">➕</div>
                <h4>Add Activity</h4>
            </a>
            <a href="modules/hours/add_hours.php" class="action-btn">
                <div style="font-size: 32px;">📋</div>
                <h4>Log Hours</h4>
            </a>
            <a href="profile.php" class="action-btn">
                <div style="font-size: 32px;">⚙️</div>
                <h4>My Profile</h4>
            </a>
            <a href="change_password.php" class="action-btn">
                <div style="font-size: 32px;">🔒</div>
                <h4>Change Password</h4>
            </a>
        </div>
        
        <div class="recent-section">
            <h3>Pending Hour Verifications</h3>
            <?php if (count($pending_submissions) > 0): ?>
                <?php foreach ($pending_submissions as $hour): ?>
                    <div class="recent-item">
                        <div class="recent-info">
                            <h4><?php echo htmlspecialchars($hour['full_name']); ?> - <?php echo htmlspecialchars($hour['activity_name']); ?></h4>
                            <p><?php echo $hour['hours_worked']; ?> hours on <?php echo date('M d, Y', strtotime($hour['work_date'])); ?></p>
                            <div class="actions">
                                <a href="modules/hours/manage_hours.php?action=verify&id=<?php echo $hour['hour_id']; ?>" 
                                   class="btn-sm btn-success"
                                   onclick="return confirm('Verify these hours?')">✓ Verify</a>
                                <a href="modules/hours/manage_hours.php?action=reject&id=<?php echo $hour['hour_id']; ?>" 
                                   class="btn-sm btn-warning"
                                   onclick="return confirm('Reject these hours?')">✗ Reject</a>
                            </div>
                        </div>
                        <span class="badge badge-pending">Pending</span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #666; text-align: center; padding: 20px;">No pending verifications</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>